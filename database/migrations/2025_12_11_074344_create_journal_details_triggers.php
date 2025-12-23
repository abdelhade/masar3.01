<?php



use Illuminate\Database\Migrations\Migration;

use Illuminate\Support\Facades\DB;



return new class extends Migration

{

    public function up(): void
    {
        // Skip stored procedures and triggers for SQLite (not supported)
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        // 1. نمسح أي حاجة قديمة أولاً (مهم جدًا للتطوير)
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_update_parent_balances');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_journal_details_after_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_journal_details_after_update');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_journal_details_after_delete');

        // 2. Stored Procedure بسيطة وسريعة: تطلع من الحساب لفوق وتحدث كل أب
        DB::unprepared('
            CREATE PROCEDURE sp_update_parent_balances(IN start_account_id BIGINT)
            BEGIN
                DECLARE current_id BIGINT DEFAULT start_account_id;
                DECLARE parent_id_val BIGINT;
                DECLARE calculated_balance DECIMAL(18,3) DEFAULT 0;
                
                loop_label: REPEAT
                    SELECT parent_id INTO parent_id_val
                    FROM acc_head
                    WHERE id = current_id AND (isdeleted = 0 OR isdeleted IS NULL);
                    
                    IF parent_id_val IS NULL THEN
                        LEAVE loop_label;
                    END IF;
                    
                    -- حساب الرصيد أولاً في متغير منفصل لتجنب مشكلة MySQL 1093
                    SELECT COALESCE(SUM(balance), 0) INTO calculated_balance
                    FROM acc_head
                    WHERE parent_id = parent_id_val
                      AND (isdeleted = 0 OR isdeleted IS NULL);
                    
                    -- ثم تحديث الجدول باستخدام المتغير
                    UPDATE acc_head
                    SET balance = calculated_balance
                    WHERE id = parent_id_val;
                    
                    SET current_id = parent_id_val;
                UNTIL parent_id_val IS NULL END REPEAT loop_label;
            END
        ');
        // 3. Trigger بعد الإضافة
        DB::unprepared('
            CREATE TRIGGER trg_journal_details_after_insert
            AFTER INSERT ON journal_details
            FOR EACH ROW
            BEGIN
                DECLARE calculated_balance DECIMAL(18,3) DEFAULT 0;
                
                -- حساب الرصيد أولاً في متغير منفصل لتجنب مشكلة MySQL 1093
                SELECT COALESCE(SUM(CAST(debit AS DECIMAL(18,3))), 0) - 
                       COALESCE(SUM(CAST(credit AS DECIMAL(18,3))), 0)
                INTO calculated_balance
                FROM journal_details
                WHERE account_id = NEW.account_id
                  AND (isdeleted = 0 OR isdeleted IS NULL);
                
                -- ثم تحديث الجدول باستخدام المتغير
                UPDATE acc_head
                SET balance = calculated_balance
                WHERE id = NEW.account_id;
                
                CALL sp_update_parent_balances(NEW.account_id);
            END
        ');
        // 4. Trigger بعد التعديل
        DB::unprepared('
            CREATE TRIGGER trg_journal_details_after_update
            AFTER UPDATE ON journal_details
            FOR EACH ROW
            BEGIN
                DECLARE calculated_balance DECIMAL(18,3) DEFAULT 0;
                
                IF OLD.account_id != NEW.account_id OR OLD.account_id IS NULL OR NEW.account_id IS NULL THEN
                    IF OLD.account_id IS NOT NULL THEN
                        -- حساب الرصيد للحساب القديم
                        SELECT COALESCE(SUM(CAST(debit AS DECIMAL(18,3))), 0) - 
                               COALESCE(SUM(CAST(credit AS DECIMAL(18,3))), 0)
                        INTO calculated_balance
                        FROM journal_details
                        WHERE account_id = OLD.account_id
                          AND (isdeleted = 0 OR isdeleted IS NULL);
                        
                        -- تحديث الجدول باستخدام المتغير
                        UPDATE acc_head
                        SET balance = calculated_balance
                        WHERE id = OLD.account_id;
                        
                        CALL sp_update_parent_balances(OLD.account_id);
                    END IF;
                    IF NEW.account_id IS NOT NULL THEN
                        -- حساب الرصيد للحساب الجديد
                        SELECT COALESCE(SUM(CAST(debit AS DECIMAL(18,3))), 0) - 
                               COALESCE(SUM(CAST(credit AS DECIMAL(18,3))), 0)
                        INTO calculated_balance
                        FROM journal_details
                        WHERE account_id = NEW.account_id
                          AND (isdeleted = 0 OR isdeleted IS NULL);
                        
                        -- تحديث الجدول باستخدام المتغير
                        UPDATE acc_head
                        SET balance = calculated_balance
                        WHERE id = NEW.account_id;
                        
                        CALL sp_update_parent_balances(NEW.account_id);
                    END IF;
                ELSE
                    -- حساب الرصيد للحساب
                    SELECT COALESCE(SUM(CAST(debit AS DECIMAL(18,3))), 0) - 
                           COALESCE(SUM(CAST(credit AS DECIMAL(18,3))), 0)
                    INTO calculated_balance
                    FROM journal_details
                    WHERE account_id = NEW.account_id
                      AND (isdeleted = 0 OR isdeleted IS NULL);
                    
                    -- تحديث الجدول باستخدام المتغير
                    UPDATE acc_head
                    SET balance = calculated_balance
                    WHERE id = NEW.account_id;
                    
                    CALL sp_update_parent_balances(NEW.account_id);
                END IF;
            END
        ');

        // 5. Trigger بعد الحذف
        DB::unprepared('
            CREATE TRIGGER trg_journal_details_after_delete
            AFTER DELETE ON journal_details
            FOR EACH ROW
            BEGIN
                DECLARE calculated_balance DECIMAL(18,3) DEFAULT 0;
                
                -- حساب الرصيد أولاً في متغير منفصل لتجنب مشكلة MySQL 1093
                SELECT COALESCE(SUM(CAST(debit AS DECIMAL(18,3))), 0) - 
                       COALESCE(SUM(CAST(credit AS DECIMAL(18,3))), 0)
                INTO calculated_balance
                FROM journal_details
                WHERE account_id = OLD.account_id
                  AND (isdeleted = 0 OR isdeleted IS NULL);
                
                -- ثم تحديث الجدول باستخدام المتغير
                UPDATE acc_head
                SET balance = calculated_balance
                WHERE id = OLD.account_id;
                
                CALL sp_update_parent_balances(OLD.account_id);
            END
        ');
    }


    public function down(): void
    {
        // Skip stored procedures and triggers for SQLite (not supported)
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::unprepared('DROP TRIGGER IF EXISTS trg_journal_details_after_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_journal_details_after_update');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_journal_details_after_delete');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_update_parent_balances');
    }

};