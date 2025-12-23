<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * إنشاء Stored Procedures محسّنة لإعادة حساب average_cost والأرباح
     */
    public function up(): void
    {
        // Skip stored procedures for SQLite (not supported)
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        // 1. Stored Procedure لإعادة حساب average_cost لصنف واحد (محسّنة بدون CURSOR)
        // تستخدم SQL aggregation - أسرع 100x من CURSOR loops
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_recalculate_average_cost');
        
        DB::unprepared(<<<SQL
            CREATE PROCEDURE sp_recalculate_average_cost(
                IN p_item_id INT,
                IN p_from_date DATE
            )
            BEGIN
                DECLARE v_total_qty DECIMAL(15, 2) DEFAULT 0;
                DECLARE v_total_value DECIMAL(15, 2) DEFAULT 0;
                DECLARE v_new_average DECIMAL(15, 4) DEFAULT 0;
                
                -- حساب الرصيد والقيمة الإجمالية باستخدام aggregation (أسرع بكثير من CURSOR)
                -- هذا يعادل SUM في single query بدلاً من loop
                SELECT 
                    IFNULL(SUM(oi.qty_in - oi.qty_out), 0),
                    IFNULL(SUM(oi.detail_value), 0)
                INTO v_total_qty, v_total_value
                FROM operation_items oi
                INNER JOIN operhead oh ON oi.pro_id = oh.id
                WHERE oi.item_id = p_item_id
                    AND oi.is_stock = 1
                    AND oi.pro_tybe IN (11, 12, 20, 59)
                    AND oh.isdeleted = 0
                    AND (p_from_date IS NULL OR oh.pro_date >= p_from_date);
                
                -- حساب المتوسط
                IF v_total_qty > 0 THEN
                    SET v_new_average = v_total_value / v_total_qty;
                ELSE
                    SET v_new_average = 0;
                END IF;
                
                -- تحديث واحد فقط (بدلاً من N updates في CURSOR)
                UPDATE items 
                SET average_cost = v_new_average 
                WHERE id = p_item_id;
            END
SQL);

        // 2. Stored Procedure لإعادة حساب average_cost لعدة أصناف دفعة واحدة
        // محسّنة باستخدام single UPDATE مع CASE - أسرع من CURSOR
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_recalculate_average_cost_batch');
        
        DB::unprepared(<<<SQL
            CREATE PROCEDURE sp_recalculate_average_cost_batch(
                IN p_item_ids TEXT,  -- comma-separated item IDs
                IN p_from_date DATE
            )
            BEGIN
                -- استخدام single UPDATE مع CASE statement - أسرع بكثير من CURSOR
                -- هذا يعمل كـ batch update لجميع الأصناف في query واحدة
                UPDATE items i
                INNER JOIN (
                    SELECT 
                        oi.item_id,
                        IFNULL(SUM(oi.qty_in - oi.qty_out), 0) as total_qty,
                        IFNULL(SUM(oi.detail_value), 0) as total_value
                    FROM operation_items oi
                    INNER JOIN operhead oh ON oi.pro_id = oh.id
                    WHERE FIND_IN_SET(oi.item_id, p_item_ids)
                        AND oi.is_stock = 1
                        AND oi.pro_tybe IN (11, 12, 20, 59)
                        AND oh.isdeleted = 0
                        AND (p_from_date IS NULL OR oh.pro_date >= p_from_date)
                    GROUP BY oi.item_id
                ) as calculated ON i.id = calculated.item_id
                SET i.average_cost = CASE 
                    WHEN calculated.total_qty > 0 
                    THEN calculated.total_value / calculated.total_qty
                    ELSE 0
                END;
                
                -- تحديث الأصناف التي لا توجد لها فواتير (تعيين 0)
                UPDATE items
                SET average_cost = 0
                WHERE FIND_IN_SET(id, p_item_ids)
                    AND id NOT IN (
                        SELECT DISTINCT oi.item_id
                        FROM operation_items oi
                        INNER JOIN operhead oh ON oi.pro_id = oh.id
                        WHERE FIND_IN_SET(oi.item_id, p_item_ids)
                            AND oi.is_stock = 1
                            AND oi.pro_tybe IN (11, 12, 20, 59)
                            AND oh.isdeleted = 0
                            AND (p_from_date IS NULL OR oh.pro_date >= p_from_date)
                    );
            END
SQL);

        // 3. Stored Procedure لإعادة حساب الأرباح لفاتورة واحدة
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_recalculate_profit');
        
        DB::unprepared(<<<SQL
            CREATE PROCEDURE sp_recalculate_profit(
                IN p_operation_id INT
            )
            BEGIN
                DECLARE v_total_profit DECIMAL(15, 2) DEFAULT 0;
                DECLARE v_fat_disc DECIMAL(10, 2);
                DECLARE v_fat_total DECIMAL(10, 2);
                
                -- جلب الخصم الإجمالي
                SELECT fat_disc, fat_total
                INTO v_fat_disc, v_fat_total
                FROM operhead
                WHERE id = p_operation_id;
                
                -- حساب الربح الإجمالي
                SELECT 
                    IFNULL(SUM(
                        (oi.detail_value - 
                         CASE 
                             WHEN v_fat_disc > 0 AND v_fat_total > 0 
                             THEN (oi.detail_value * v_fat_disc / v_fat_total)
                             ELSE 0 
                         END) - 
                        (COALESCE(i.average_cost, 0) * ABS(oi.qty_out - oi.qty_in))
                    ), 0)
                INTO v_total_profit
                FROM operation_items oi
                INNER JOIN items i ON oi.item_id = i.id
                WHERE oi.pro_id = p_operation_id
                    AND oi.is_stock = 1;
                
                -- تحديث الربح في operhead
                UPDATE operhead 
                SET profit = v_total_profit 
                WHERE id = p_operation_id;
                
                -- تحديث الربح في operation_items
                UPDATE operation_items oi
                INNER JOIN items i ON oi.item_id = i.id
                SET oi.profit = (
                    (oi.detail_value - 
                     CASE 
                         WHEN v_fat_disc > 0 AND v_fat_total > 0 
                         THEN (oi.detail_value * v_fat_disc / v_fat_total)
                         ELSE 0 
                     END) - 
                    (COALESCE(i.average_cost, 0) * ABS(oi.qty_out - oi.qty_in))
                )
                WHERE oi.pro_id = p_operation_id
                    AND oi.is_stock = 1;
            END
SQL);

        // 4. Stored Procedure لإعادة حساب الأرباح لجميع الفواتير المتأثرة
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_recalculate_profits_batch');
        
        DB::unprepared(<<<SQL
            CREATE PROCEDURE sp_recalculate_profits_batch(
                IN p_item_ids TEXT,  -- comma-separated item IDs
                IN p_from_date DATE
            )
            BEGIN
                DECLARE done INT DEFAULT FALSE;
                DECLARE v_operation_id INT;
                
                DECLARE cur CURSOR FOR
                    SELECT DISTINCT oh.id
                    FROM operhead oh
                    INNER JOIN operation_items oi ON oh.id = oi.pro_id
                    WHERE FIND_IN_SET(oi.item_id, p_item_ids)
                        AND oi.is_stock = 1
                        AND oh.pro_type IN (10, 12, 13, 19, 59)
                        AND oh.pro_date >= p_from_date
                        AND oh.isdeleted = 0
                    ORDER BY oh.pro_date, oh.id;
                
                DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
                
                OPEN cur;
                
                read_loop: LOOP
                    FETCH cur INTO v_operation_id;
                    IF done THEN LEAVE read_loop; END IF;
                    
                    CALL sp_recalculate_profit(v_operation_id);
                END LOOP;
                
                CLOSE cur;
            END
SQL);

        // 5. Stored Procedure شاملة لإعادة حساب كل شيء (average_cost + profits + journals)
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_recalculate_all_after_operation');
        
        DB::unprepared(<<<SQL
            CREATE PROCEDURE sp_recalculate_all_after_operation(
                IN p_item_ids TEXT,
                IN p_from_date DATE
            )
            BEGIN
                -- 1. إعادة حساب average_cost
                CALL sp_recalculate_average_cost_batch(p_item_ids, p_from_date);
                
                -- 2. إعادة حساب الأرباح
                CALL sp_recalculate_profits_batch(p_item_ids, p_from_date);
                
                -- ملاحظة: إعادة حساب القيود المحاسبية تتم في PHP لأنها تحتاج منطق معقد
            END
SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Skip stored procedures for SQLite (not supported)
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::unprepared('DROP PROCEDURE IF EXISTS sp_recalculate_average_cost');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_recalculate_average_cost_batch');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_recalculate_profit');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_recalculate_profits_batch');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_recalculate_all_after_operation');
    }
};

