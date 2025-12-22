<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

class DatabaseFreshSafeCommand extends Command
{
    protected $signature = 'db:fresh-safe
                            {--tables=* : Tables to exclude from backup (comma-separated)}
                            {--seed : Run seeders after restore}
                            {--compress : Compress backup file}
                            {--chunk-size=1000 : Chunk size for large tables}
                            {--skip-large : Skip large tables (more than 10000 rows)}';

    protected $description = 'Run migrate:fresh while preserving data from specified tables (optimized for large data)';

    private array $excludedTables = ['migrations', 'failed_jobs', 'password_reset_tokens', 'sessions', 'cache', 'cache_locks'];

    private int $chunkSize = 1000;

    private bool $compress = false;

    private bool $skipLarge = false;

    public function handle(): int
    {
        $this->excludedTables = array_merge($this->excludedTables, $this->option('tables'));
        $this->chunkSize = (int) $this->option('chunk-size');
        $this->compress = $this->option('compress');
        $this->skipLarge = $this->option('skip-large');

        $this->info('🔄 Starting Fresh Safe process for database...');
        $this->newLine();

        try {
            // 1. Check connection
            $this->info('📡 Checking database connection...');
            DB::connection()->getPdo();
            $this->info('✅ Connection successful');
            $this->newLine();

            // 2. Create backup
            $backupPath = $this->createBackup();
            if (! $backupPath) {
                $this->error('❌ Failed to create backup');

                return Command::FAILURE;
            }

            // 3. migrate:fresh
            $this->info('🗑️  Running migrate:fresh...');
            $this->call('migrate:fresh', ['--force' => true]);
            $this->info('✅ migrate:fresh completed successfully');
            $this->newLine();

            // 4. Restore data
            $this->info('📥 Restoring data from backup...');
            $this->restoreData($backupPath);
            $this->info('✅ Data restored successfully');
            $this->newLine();

            // 5. Seeders (optional)
            if ($this->option('seed')) {
                $this->info('🌱 Running seeders...');
                $this->call('db:seed', ['--force' => true]);
                $this->info('✅ Seeders completed');
            }

            // 6. Clean temporary files
            if (File::exists($backupPath)) {
                File::delete($backupPath);
                $this->info('🧹 Temporary files deleted');
            }

            $this->newLine();
            $this->info('✅ Process completed successfully!');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ An error occurred: '.$e->getMessage());
            $this->error('📍 File: '.$e->getFile().':'.$e->getLine());

            return Command::FAILURE;
        }
    }

    private function createBackup(): ?string
    {
        $this->info('💾 Creating backup...');

        // Try using mysqldump first (fastest and best)
        $mysqldumpPath = $this->createBackupWithMysqldump();
        if ($mysqldumpPath) {
            return $mysqldumpPath;
        }

        // Fallback: Use Laravel (optimized for large data)
        $this->warn('⚠️  mysqldump not available, using alternative method (optimized for large data)...');

        return $this->createBackupWithLaravel();
    }

    private function createBackupWithMysqldump(): ?string
    {
        try {
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $host = config('database.connections.mysql.host');
            $port = config('database.connections.mysql.port', 3306);

            $timestamp = now()->format('YmdHis');
            $backupDir = storage_path('app/backups');
            File::ensureDirectoryExists($backupDir);

            $backupFile = $backupDir.'/backup_'.$timestamp.'.sql';
            $compressedFile = $backupFile.'.gz';

            // بناء قائمة الجداول المستثناة
            $excludeTables = implode(' ', array_map(fn ($t) => "--ignore-table={$database}.{$t}", $this->excludedTables));

            // بناء أمر mysqldump (بدون shell redirection ليعمل على Windows و Linux)
            // --no-create-info: لحفظ البيانات فقط بدون structure (الجداول تُنشأ من migrations)
            // --complete-insert: لكتابة أسماء الأعمدة في INSERT statements
            // --skip-triggers --skip-routines: لتجنب مشاكل الصلاحيات
            $command = sprintf(
                'mysqldump --user=%s --password=%s --host=%s --port=%s --single-transaction --quick --lock-tables=false --no-create-info --complete-insert --skip-triggers --skip-routines %s %s',
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($host),
                escapeshellarg($port),
                $excludeTables,
                escapeshellarg($database)
            );

            $this->info('⏳ Creating backup (may take time with large data)...');

            // Execute mysqldump and get output
            $process = Process::timeout(3600)->run($command);

            if (! $process->successful()) {
                $this->warn('⚠️  mysqldump failed: '.$process->errorOutput());

                return null;
            }

            // Save output to file
            $output = $process->output();
            File::put($backupFile, $output);

            // Compress file if requested
            if ($this->compress) {
                $this->info('🗜️  Compressing file...');
                $this->compressFile($backupFile, $compressedFile);
                File::delete($backupFile);
                $finalFile = $compressedFile;
            } else {
                $finalFile = $backupFile;
            }

            $size = File::size($finalFile);
            $this->info('✅ Backup created: '.$this->formatBytes($size));

            return $finalFile;
        } catch (\Exception $e) {
            $this->warn('⚠️  Error in mysqldump: '.$e->getMessage());

            return null;
        }
    }

    private function createBackupWithLaravel(): ?string
    {
        try {
            $timestamp = now()->format('YmdHis');
            $backupDir = storage_path('app/backups');
            File::ensureDirectoryExists($backupDir);

            $backupFile = $backupDir.'/backup_'.$timestamp.'.sql';
            $handle = fopen($backupFile, 'w');

            if (! $handle) {
                throw new \Exception('Failed to open backup file');
            }

            // Header
            $this->writeSqlHeader($handle);

            // Get list of tables
            $tables = $this->getTablesToBackup();
            $totalTables = count($tables);

            $this->info("📊 Number of tables to backup: {$totalTables}");

            $progressBar = $this->output->createProgressBar($totalTables);
            $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% - %message%');
            $progressBar->setMessage('Starting backup...');
            $progressBar->start();

            foreach ($tables as $index => $tableName) {
                $progressBar->setMessage("Processing: {$tableName}");

                // Check table size
                $rowCount = $this->getTableRowCount($tableName);

                if ($this->skipLarge && $rowCount > 10000) {
                    $progressBar->setMessage("⏭️  Skipping {$tableName} (large: {$rowCount} rows)");
                    $progressBar->advance();

                    continue;
                }

                // Backup data (with Chunking for large tables)
                $this->backupTableData($handle, $tableName, $rowCount);

                $progressBar->advance();
            }

            $progressBar->setMessage('Completed!');
            $progressBar->finish();
            $this->newLine(2);

            // Footer
            fwrite($handle, "\nSET FOREIGN_KEY_CHECKS=1;\n");
            fwrite($handle, "COMMIT;\n");
            fclose($handle);

            // Compress file if requested
            if ($this->compress) {
                $compressedFile = $backupFile.'.gz';
                $this->info('🗜️  Compressing file...');
                $this->compressFile($backupFile, $compressedFile);
                File::delete($backupFile);
                $backupFile = $compressedFile;
            }

            $size = File::size($backupFile);
            $this->info('✅ Backup created: '.$this->formatBytes($size));

            return $backupFile;
        } catch (\Exception $e) {
            $this->error('❌ Error in backup: '.$e->getMessage());

            return null;
        }
    }

    private function getTablesToBackup(): array
    {
        $tables = DB::select('SHOW TABLES');
        $database = DB::getDatabaseName();
        $tableKey = 'Tables_in_'.$database;

        return collect($tables)
            ->pluck($tableKey)
            ->reject(fn ($table) => in_array($table, $this->excludedTables))
            ->values()
            ->toArray();
    }

    private function getTableRowCount(string $tableName): int
    {
        try {
            $result = DB::selectOne("SELECT COUNT(*) as count FROM `{$tableName}`");

            return (int) ($result->count ?? 0);
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function hasIdColumn(string $tableName): bool
    {
        try {
            $columns = DB::select("SHOW COLUMNS FROM `{$tableName}` WHERE Field = 'id'");

            return count($columns) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function getPrimaryKeyColumns(string $tableName): array
    {
        try {
            $keys = DB::select("SHOW KEYS FROM `{$tableName}` WHERE Key_name = 'PRIMARY'");

            // Sort by Seq_in_index to ensure correct order
            usort($keys, fn ($a, $b) => $a->Seq_in_index <=> $b->Seq_in_index);

            return array_map(fn ($key) => $key->Column_name, $keys);
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getFirstColumn(string $tableName): ?string
    {
        try {
            $columns = DB::select("SHOW COLUMNS FROM `{$tableName}` LIMIT 1");

            return ! empty($columns) ? $columns[0]->Field : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function backupTableData($handle, string $tableName, int $rowCount): void
    {
        fwrite($handle, "\n-- Table: {$tableName} ({$rowCount} rows)\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");

        if ($rowCount === 0) {
            fwrite($handle, "-- Table is empty\n");

            return;
        }

        // للجداول الصغيرة: نسخ مباشر
        if ($rowCount <= $this->chunkSize) {
            $rows = DB::table($tableName)->get();
            $this->writeTableData($handle, $tableName, $rows);

            return;
        }

        // For large tables: Chunking
        $this->info("  📦 Processing {$tableName} in chunks ({$rowCount} rows)...");

        $totalChunks = (int) ceil($rowCount / $this->chunkSize);
        $chunkProgress = $this->output->createProgressBar($totalChunks);
        $chunkProgress->setFormat('    %current%/%max% [%bar%] %percent:3s%%');
        $chunkProgress->start();

        // Check if table has 'id' column for efficient chunking
        if ($this->hasIdColumn($tableName)) {
            // Use chunkById for tables with id column (more efficient)
            DB::table($tableName)
                ->orderBy('id')
                ->chunkById($this->chunkSize, function ($rows) use ($handle, $tableName, $chunkProgress) {
                    $this->writeTableData($handle, $tableName, $rows);
                    $chunkProgress->advance();
                });
        } else {
            // Use regular chunk for tables without id column (composite keys, etc.)
            // Get first primary key column for ordering (or first column if no primary key)
            $primaryKeyColumns = $this->getPrimaryKeyColumns($tableName);
            $orderColumn = ! empty($primaryKeyColumns) ? $primaryKeyColumns[0] : $this->getFirstColumn($tableName);

            // Validate orderColumn is not empty
            if (empty($orderColumn)) {
                throw new \Exception("Cannot determine order column for table: {$tableName}");
            }

            // Always use orderBy with chunk (required by Laravel)
            DB::table($tableName)
                ->orderBy($orderColumn)
                ->chunk($this->chunkSize, function ($rows) use ($handle, $tableName, $chunkProgress) {
                    $this->writeTableData($handle, $tableName, $rows);
                    $chunkProgress->advance();
                });
        }

        $chunkProgress->finish();
        $this->newLine();
    }

    private function writeTableData($handle, string $tableName, $rows): void
    {
        if ($rows->isEmpty()) {
            return;
        }

        $columns = array_keys((array) $rows->first());

        foreach ($rows as $row) {
            $values = array_map(function ($value) {
                if (is_null($value)) {
                    return 'NULL';
                }
                if (is_bool($value)) {
                    return $value ? '1' : '0';
                }
                if (is_string($value)) {
                    return "'".addslashes($value)."'";
                }

                return $value;
            }, array_values((array) $row));

            $sql = "INSERT INTO `{$tableName}` (`".
                implode('`, `', $columns).
                '`) VALUES ('.
                implode(', ', $values).
                ");\n";

            fwrite($handle, $sql);
        }
    }

    private function writeSqlHeader($handle): void
    {
        $database = config('database.connections.mysql.database');
        $exportDate = now()->format('Y-m-d H:i:s');

        $header = "-- =============================================\n".
            "-- Database Backup (Fresh Safe)\n".
            "-- =============================================\n".
            "-- Database: {$database}\n".
            "-- Export Date: {$exportDate}\n".
            '-- Laravel Version: '.app()->version()."\n".
            '-- PHP Version: '.PHP_VERSION."\n".
            "-- =============================================\n\n".
            "SET FOREIGN_KEY_CHECKS=0;\n".
            "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n".
            "START TRANSACTION;\n".
            "SET time_zone = '+00:00';\n\n";

        fwrite($handle, $header);
    }

    private function restoreData(string $backupPath): void
    {
        // If file is compressed, decompress first
        if (str_ends_with($backupPath, '.gz')) {
            $this->info('📦 Decompressing file...');
            $uncompressedPath = str_replace('.gz', '', $backupPath);
            $this->decompressFile($backupPath, $uncompressedPath);
            $backupPath = $uncompressedPath;
        }

        $this->info('⏳ Restoring data (may take time)...');

        // Check if file exists
        if (! File::exists($backupPath)) {
            throw new \Exception("Backup file not found: {$backupPath}");
        }

        // Read SQL file and parse it
        $sqlContent = File::get($backupPath);

        // Parse SQL file to extract INSERT statements
        $this->restoreDataFromSql($sqlContent);

        // Delete uncompressed file if temporary
        if (str_contains($backupPath, 'backup_') && File::exists($backupPath)) {
            File::delete($backupPath);
        }
    }

    private function restoreDataFromSql(string $sqlContent): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Try to use mysql command line first (faster for large files)
        // If it fails, fall back to Laravel method
        if ($this->tryRestoreWithMysql($sqlContent)) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            return;
        }

        // Fallback: Use Laravel DB facade with smart column matching
        $this->warn('⚠️  Falling back to Laravel restore method (slower but handles schema changes)...');

        // Parse SQL content to extract INSERT statements
        $insertStatements = $this->extractInsertStatements($sqlContent);

        if (empty($insertStatements)) {
            $this->warn('⚠️  No INSERT statements found in backup file');
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            return;
        }

        $totalStatements = count($insertStatements);
        $progressBar = $this->output->createProgressBar($totalStatements);
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% - %message%');
        $progressBar->setMessage('Restoring data...');
        $progressBar->start();

        $tablesProcessed = [];
        foreach ($insertStatements as $statement) {
            $tableName = $statement['table'];
            if (! isset($tablesProcessed[$tableName])) {
                $tablesProcessed[$tableName] = 0;
            }
            $tablesProcessed[$tableName]++;

            $this->processInsertStatement($statement, $tableName);
            $progressBar->setMessage("Restoring: {$tableName} ({$tablesProcessed[$tableName]} rows)");
            $progressBar->advance();
        }

        $progressBar->setMessage('Completed!');
        $progressBar->finish();
        $this->newLine(2);

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    private function tryRestoreWithMysql(string $sqlContent): bool
    {
        try {
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $host = config('database.connections.mysql.host');
            $port = config('database.connections.mysql.port', 3306);

            // Build mysql command
            $command = sprintf(
                'mysql --user=%s --password=%s --host=%s --port=%s %s',
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($database)
            );

            // Execute command with SQL content passed via stdin
            $process = Process::timeout(3600)
                ->input($sqlContent)
                ->run($command);

            if ($process->successful()) {
                return true;
            }

            // Check if error is due to missing columns (schema mismatch)
            $errorOutput = $process->errorOutput();
            if (str_contains($errorOutput, 'Unknown column') || str_contains($errorOutput, "doesn't exist")) {
                // Schema mismatch - need to use Laravel method
                return false;
            }

            // Other error - log and return false
            $this->warn('⚠️  mysql command failed: '.$errorOutput);

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function extractInsertStatements(string $sqlContent): array
    {
        $statements = [];
        $lines = explode("\n", $sqlContent);
        $currentStatement = null;
        $currentTable = null;

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip comments and empty lines
            if (empty($line) || str_starts_with($line, '--') || str_starts_with($line, '/*')) {
                continue;
            }

            // Skip SET statements
            if (preg_match('/^SET\s+/i', $line) || preg_match('/^START\s+TRANSACTION/i', $line) || preg_match('/^COMMIT/i', $line)) {
                continue;
            }

            // Start of INSERT statement
            if (preg_match('/^INSERT\s+INTO\s+`?(\w+)`?\s*\(/i', $line, $matches)) {
                // Save previous statement if exists
                if ($currentStatement !== null && $currentTable !== null) {
                    $statements[] = [
                        'table' => $currentTable,
                        'sql' => $currentStatement,
                    ];
                }

                $currentTable = $matches[1];
                $currentStatement = $line;
            } elseif ($currentStatement !== null) {
                // Continuation of INSERT statement (multi-line)
                $currentStatement .= ' '.$line;
            }

            // Check if statement is complete (ends with semicolon)
            if ($currentStatement !== null && str_ends_with($line, ';')) {
                $statements[] = [
                    'table' => $currentTable,
                    'sql' => $currentStatement,
                ];
                $currentStatement = null;
                $currentTable = null;
            }
        }

        // Add last statement if exists
        if ($currentStatement !== null && $currentTable !== null) {
            $statements[] = [
                'table' => $currentTable,
                'sql' => $currentStatement,
            ];
        }

        return $statements;
    }

    private function processInsertStatement(array $statement, string $tableName): void
    {
        try {
            $sql = $statement['sql'];

            // Extract columns and values from INSERT statement
            // Handle both single and multi-value INSERTs
            if (preg_match('/^INSERT\s+INTO\s+`?(\w+)`?\s*\(([^)]+)\)\s*VALUES\s*(.+?);?$/is', $sql, $matches)) {
                $backupColumns = array_map(function ($col) {
                    return trim(str_replace(['`', "'", '"'], '', $col));
                }, explode(',', $matches[2]));

                // Get current table structure (after migrations)
                $currentColumns = $this->getTableColumns($tableName);

                if (empty($currentColumns)) {
                    // Table doesn't exist, skip
                    return;
                }

                // Parse values - handle both single and multi-row inserts
                $valuesString = trim($matches[3]);
                $valuesString = rtrim($valuesString, ';');

                // Split by ),( to handle multi-row inserts
                $valueRows = preg_split('/\)\s*,\s*\(/', $valuesString);
                $valueRows[0] = ltrim($valueRows[0], '(');
                $valueRows[count($valueRows) - 1] = rtrim($valueRows[count($valueRows) - 1], ')');

                foreach ($valueRows as $valueRow) {
                    $backupValues = $this->parseValues($valueRow);

                    // Build data array matching current table structure
                    $data = [];
                    foreach ($currentColumns as $column) {
                        $columnName = $column['name'];
                        $columnIndex = array_search($columnName, $backupColumns);

                        if ($columnIndex !== false && isset($backupValues[$columnIndex])) {
                            // Column exists in backup, use its value
                            $value = $backupValues[$columnIndex];
                            $data[$columnName] = $this->parseValue($value, $column['type']);
                        } else {
                            // New column not in backup - use default or NULL
                            if ($column['nullable'] || $column['default'] !== null) {
                                $data[$columnName] = $column['default'];
                            } else {
                                // For required columns without default, use appropriate default based on type
                                $data[$columnName] = $this->getDefaultValueForType($column['type']);
                            }
                        }
                    }

                    // Insert data using Laravel (handles all edge cases)
                    if (! empty($data)) {
                        DB::table($tableName)->insert($data);
                    }
                }
            }
        } catch (\Exception $e) {
            // Log error but continue with other rows
            $this->warn("⚠️  Error inserting into {$tableName}: ".$e->getMessage());
        }
    }

    private function getTableColumns(string $tableName): array
    {
        try {
            $columns = DB::select("SHOW COLUMNS FROM `{$tableName}`");
            $result = [];

            foreach ($columns as $column) {
                $column = (array) $column;
                $result[] = [
                    'name' => $column['Field'],
                    'type' => $column['Type'],
                    'nullable' => $column['Null'] === 'YES',
                    'default' => $column['Default'],
                ];
            }

            return $result;
        } catch (\Exception $e) {
            return [];
        }
    }

    private function parseValues(string $valuesString): array
    {
        $values = [];
        $current = '';
        $inQuotes = false;
        $quoteChar = null;
        $depth = 0;
        $length = strlen($valuesString);

        for ($i = 0; $i < $length; $i++) {
            $char = $valuesString[$i];
            $prevChar = $i > 0 ? $valuesString[$i - 1] : null;

            if (! $inQuotes && ($char === '"' || $char === "'")) {
                $inQuotes = true;
                $quoteChar = $char;
                $current .= $char;
            } elseif ($inQuotes && $char === $quoteChar) {
                // Check if it's escaped
                if ($prevChar === '\\') {
                    $current .= $char;
                } else {
                    // Check if next char is also quote (escaped quote in SQL)
                    $nextChar = $i < $length - 1 ? $valuesString[$i + 1] : null;
                    if ($nextChar === $quoteChar) {
                        $current .= $char.$nextChar;
                        $i++; // Skip next char
                    } else {
                        $inQuotes = false;
                        $quoteChar = null;
                        $current .= $char;
                    }
                }
            } elseif (! $inQuotes && $char === '(') {
                $depth++;
                $current .= $char;
            } elseif (! $inQuotes && $char === ')') {
                $depth--;
                $current .= $char;
            } elseif (! $inQuotes && $char === ',' && $depth === 0) {
                $values[] = trim($current);
                $current = '';
            } else {
                $current .= $char;
            }
        }

        if (! empty($current)) {
            $values[] = trim($current);
        }

        return $values;
    }

    private function parseValue(string $value, string $columnType): mixed
    {
        $value = trim($value);

        // Handle NULL
        if (strtoupper($value) === 'NULL') {
            return null;
        }

        // Remove quotes
        if ((str_starts_with($value, "'") && str_ends_with($value, "'")) || (str_starts_with($value, '"') && str_ends_with($value, '"'))) {
            $value = substr($value, 1, -1);
            // Unescape
            $value = str_replace(["\\'", '\\"', '\\\\'], ["'", '"', '\\'], $value);
        }

        // Handle boolean
        if (in_array(strtolower($value), ['true', 'false', '1', '0'])) {
            return in_array(strtolower($value), ['true', '1']);
        }

        // Handle numeric types
        if (preg_match('/^(int|tinyint|smallint|mediumint|bigint)/i', $columnType)) {
            return (int) $value;
        }

        if (preg_match('/^(float|double|decimal)/i', $columnType)) {
            return (float) $value;
        }

        return $value;
    }

    private function getDefaultValueForType(string $columnType): mixed
    {
        // Handle integer types
        if (preg_match('/^(int|tinyint|smallint|mediumint|bigint)/i', $columnType)) {
            return 0;
        }

        // Handle float types
        if (preg_match('/^(float|double|decimal)/i', $columnType)) {
            return 0.0;
        }

        // Handle datetime/timestamp types
        if (preg_match('/^(datetime|timestamp)/i', $columnType)) {
            return now();
        }

        // Handle date type
        if (preg_match('/^(date)/i', $columnType)) {
            return now()->toDateString();
        }

        // Handle time type
        if (preg_match('/^(time)/i', $columnType)) {
            return now()->toTimeString();
        }

        // Handle boolean/tinyint(1)
        if (preg_match('/^(tinyint\(1\)|boolean)/i', $columnType)) {
            return false;
        }

        // Default: empty string for text types
        return '';
    }

    private function compressFile(string $source, string $destination): void
    {
        $handle = fopen($source, 'rb');
        $gzHandle = gzopen($destination, 'wb9');

        if (! $handle || ! $gzHandle) {
            throw new \Exception('Failed to open files for compression');
        }

        while (! feof($handle)) {
            $chunk = fread($handle, 8192);
            gzwrite($gzHandle, $chunk);
        }

        fclose($handle);
        gzclose($gzHandle);
    }

    private function decompressFile(string $source, string $destination): void
    {
        $gzHandle = gzopen($source, 'rb');
        $handle = fopen($destination, 'wb');

        if (! $handle || ! $gzHandle) {
            throw new \Exception('Failed to open files for decompression');
        }

        while (! gzeof($gzHandle)) {
            $chunk = gzread($gzHandle, 8192);
            fwrite($handle, $chunk);
        }

        gzclose($gzHandle);
        fclose($handle);
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision).' '.$units[$i];
    }
}
