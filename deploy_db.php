<?php
set_time_limit(0);
ini_set('memory_limit', '512M');
ini_set('max_execution_time', '0');

header('Content-Type: text/plain; charset=utf-8');
ob_implicit_flush(true);
ob_end_flush();

echo "=== D Star POS - Database Deployment ===\n";
echo "Started: " . date('Y-m-d H:i:s') . "\n\n";

$host = '127.0.0.200';
$port = '5432';
$dbname = 'dstaixqj_pos_db';
$user = 'dstaixqj_pos_user';
$password = '5dOxq)P$c{.#[}bK';

$dumpFile = __DIR__ . '/laravel-app/database_dump_clean.sql';

if (!file_exists($dumpFile)) {
    die("ERROR: SQL dump file not found at: $dumpFile\n");
}

echo "SQL Dump File: $dumpFile\n";
echo "File Size: " . round(filesize($dumpFile) / 1024 / 1024, 2) . " MB\n\n";

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    
    echo "Connected to PostgreSQL at $host:$port/$dbname\n\n";
    
    echo "Step 1: Dropping all existing tables...\n";
    
    $tables = $pdo->query("
        SELECT tablename FROM pg_catalog.pg_tables 
        WHERE schemaname = 'public'
    ")->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Found " . count($tables) . " tables\n";
    
    $pdo->exec("DROP SCHEMA public CASCADE");
    $pdo->exec("CREATE SCHEMA public");
    $pdo->exec("GRANT ALL ON SCHEMA public TO public");
    $pdo->exec("GRANT ALL ON SCHEMA public TO $user");
    
    echo "All tables dropped successfully.\n\n";
    
    echo "Step 2: Importing database dump...\n";
    
    $sql = file_get_contents($dumpFile);
    $statements = 0;
    $errors = 0;
    $startTime = microtime(true);
    
    $len = strlen($sql);
    $pos = 0;
    $currentStatement = '';
    $inString = false;
    $inComment = false;
    $stringChar = '';
    
    while ($pos < $len) {
        $char = $sql[$pos];
        
        if ($inComment) {
            if ($char === "\n") {
                $inComment = false;
            }
            $pos++;
            continue;
        }
        
        if (!$inString) {
            if ($char === '-' && $pos + 1 < $len && $sql[$pos + 1] === '-') {
                $inComment = true;
                $pos += 2;
                continue;
            }
        }
        
        if ($inString) {
            $currentStatement .= $char;
            if ($char === $stringChar) {
                if ($pos + 1 < $len && $sql[$pos + 1] === $stringChar) {
                    $currentStatement .= $sql[$pos + 1];
                    $pos += 2;
                    continue;
                }
                $inString = false;
                $stringChar = '';
            }
            $pos++;
            continue;
        }
        
        if ($char === "'" || $char === '"') {
            $inString = true;
            $stringChar = $char;
            $currentStatement .= $char;
            $pos++;
            continue;
        }
        
        if ($char === ';') {
            $stmt = trim($currentStatement);
            $currentStatement = '';
            
            if ($stmt === '' || strpos($stmt, '--') === 0) {
                $pos++;
                continue;
            }
            
            if (preg_match('/^\\\\.*$/m', $stmt)) {
                $pos++;
                continue;
            }
            
            try {
                $pdo->exec($stmt);
                $statements++;
                
                if ($statements % 500 === 0) {
                    $elapsed = round(microtime(true) - $startTime, 1);
                    $progress = round(($pos / $len) * 100, 1);
                    echo "[{$statements} stmts, {$progress}%, {$elapsed}s]\n";
                }
            } catch (PDOException $e) {
                $errorMsg = $e->getMessage();
                if (strpos($errorMsg, 'already exists') === false && 
                    strpos($errorMsg, 'does not exist') === false) {
                    $errors++;
                    if ($errors <= 20) {
                        echo "ERROR [stmt $statements]: " . substr($errorMsg, 0, 200) . "\n";
                        echo "  SQL: " . substr($stmt, 0, 100) . "...\n";
                    }
                }
            }
            
            $pos++;
            continue;
        }
        
        $currentStatement .= $char;
        $pos++;
    }
    
    $elapsed = round(microtime(true) - $startTime, 1);
    echo "\nDone! Executed $statements statements in {$elapsed}s\n";
    if ($errors > 0) {
        echo "$errors non-critical errors (already exists / does not exist)\n";
    }
    
    echo "\nStep 3: Setting up sequences...\n";
    $sequences = $pdo->query("
        SELECT sequence_name FROM information_schema.sequences 
        WHERE sequence_schema = 'public'
    ")->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($sequences as $seq) {
        try {
            $pdo->exec("ALTER SEQUENCE \"$seq\" OWNED BY NONE");
        } catch (PDOException $e) {}
    }
    
    echo "Verified " . count($sequences) . " sequences\n\n";
    
    echo "Step 4: Verifying deployment...\n";
    $tableCount = $pdo->query("SELECT count(*) FROM pg_catalog.pg_tables WHERE schemaname = 'public'")->fetchColumn();
    echo "Total tables: $tableCount\n";
    
    echo "\n=== DEPLOYMENT COMPLETED SUCCESSFULLY ===\n";
    echo "Completed: " . date('Y-m-d H:i:s') . "\n";
    
} catch (PDOException $e) {
    die("FATAL ERROR: " . $e->getMessage() . "\n");
}
