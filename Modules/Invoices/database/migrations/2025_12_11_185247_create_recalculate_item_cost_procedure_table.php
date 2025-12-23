<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Skip stored procedures for SQLite (not supported)
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::unprepared('DROP PROCEDURE IF EXISTS RecalculateItemCost');

        DB::unprepared(<<<SQL
            CREATE PROCEDURE RecalculateItemCost(
                IN p_item_id INT,
                IN p_start_date DATE,
                IN p_tenant_id INT
            )
            BEGIN
                -- تعريف المتغيرات
                DECLARE done INT DEFAULT FALSE;
                DECLARE v_id BIGINT; -- رقم سطر الصنف
                DECLARE v_pro_id BIGINT; -- رقم الفاتورة الأب (لتحديثها لاحقاً)
                DECLARE v_qty_in DECIMAL(10, 2);
                DECLARE v_qty_out DECIMAL(10, 2);
                DECLARE v_price DECIMAL(10, 2);
                DECLARE v_unit_value DECIMAL(10, 3);

                DECLARE v_current_stock DECIMAL(15, 2) DEFAULT 0;
                DECLARE v_current_avg_cost DECIMAL(15, 4) DEFAULT 0;
                DECLARE v_new_profit DECIMAL(10, 2) DEFAULT 0;

                -- الكيرسور: يرتب بناءً على تاريخ الفاتورة ثم وقت الإنشاء
                DECLARE cur CURSOR FOR
                    SELECT
                        oi.id, oi.pro_id, oi.qty_in, oi.qty_out, oi.item_price, oi.unit_value
                    FROM operation_items oi
                    JOIN operhead oh ON oi.pro_id = oh.id
                    WHERE oi.item_id = p_item_id
                      AND oi.tenant = p_tenant_id
                      AND oi.is_stock = 1
                      AND oh.pro_date >= p_start_date
                      AND oh.isdeleted = 0
                    ORDER BY oh.pro_date ASC, oh.created_at ASC, oi.id ASC;

                DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

                -- 1. نقطة البداية (الرصيد السابق)
                SELECT IFNULL(SUM(qty_in - qty_out), 0)
                INTO v_current_stock
                FROM operation_items oi
                JOIN operhead oh ON oi.pro_id = oh.id
                WHERE oi.item_id = p_item_id
                  AND oi.tenant = p_tenant_id
                  AND oi.is_stock = 1
                  AND oh.pro_date < p_start_date
                  AND oh.isdeleted = 0;

                -- 2. نقطة البداية (متوسط التكلفة السابق)
                SELECT cost_price
                INTO v_current_avg_cost
                FROM operation_items oi
                JOIN operhead oh ON oi.pro_id = oh.id
                WHERE oi.item_id = p_item_id
                  AND oi.tenant = p_tenant_id
                  AND oi.is_stock = 1
                  AND oh.pro_date < p_start_date
                  AND oh.isdeleted = 0
                ORDER BY oh.pro_date DESC, oh.created_at DESC, oi.id DESC
                LIMIT 1;

                IF v_current_avg_cost IS NULL THEN
                    SET v_current_avg_cost = 0;
                END IF;

                OPEN cur;
                read_loop: LOOP
                    FETCH cur INTO v_id, v_pro_id, v_qty_in, v_qty_out, v_price, v_unit_value;
                    IF done THEN LEAVE read_loop; END IF;

                    -- معالجة الوارد
                    IF v_qty_in > 0 THEN
                        IF v_current_stock <= 0 THEN
                            SET v_current_avg_cost = v_price;
                        ELSE
                            IF (v_current_stock + v_qty_in) > 0 THEN
                                SET v_current_avg_cost = ((v_current_stock * v_current_avg_cost) + (v_qty_in * v_price)) / (v_current_stock + v_qty_in);
                            END IF;
                        END IF;

                        SET v_current_stock = v_current_stock + v_qty_in;
                        UPDATE operation_items SET cost_price = v_current_avg_cost WHERE id = v_id;

                    -- معالجة الصادر
                    ELSEIF v_qty_out > 0 THEN
                        SET v_new_profit = (v_price - v_current_avg_cost) * v_qty_out;
                        SET v_current_stock = v_current_stock - v_qty_out;

                        UPDATE operation_items SET cost_price = v_current_avg_cost, profit = v_new_profit WHERE id = v_id;
                    END IF;

                END LOOP;
                CLOSE cur;

                -- 3. تحديث OperHead (إجمالي الربح للفواتير المتأثرة)
                -- نحدث فقط الفواتير التي تحتوي على الصنف الذي تمت إعادة حسابه وفي الفترة الزمنية المحددة
                UPDATE operhead
                INNER JOIN (
                    SELECT pro_id, SUM(profit) as total_profit
                    FROM operation_items
                    WHERE pro_id IN (
                        SELECT DISTINCT oi.pro_id
                        FROM operation_items oi
                        JOIN operhead oh ON oi.pro_id = oh.id
                        WHERE oi.item_id = p_item_id
                          AND oh.pro_date >= p_start_date
                    )
                    GROUP BY pro_id
                ) as calculated_profits ON operhead.id = calculated_profits.pro_id
                SET operhead.profit = calculated_profits.total_profit;

            END
SQL);
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS RecalculateItemCost');
    }
};
