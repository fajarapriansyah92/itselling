<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use PDO;

#[Signature('db:save')]
#[Description('Export seluruh isi database lokal ke database/backup.sql agar ikut ke-push ke Git')]
class DbSaveCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $pdo = DB::connection()->getPdo();
        $databaseName = DB::connection()->getDatabaseName();

        $tablesResult = DB::select("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");

        $sql = "-- Database Backup for {$databaseName}\n";
        $sql .= '-- Generated at: '.now()->toDateTimeString()."\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tablesResult as $row) {
            $rowArray = (array) $row;
            $table = (string) reset($rowArray);

            $createTableResult = DB::selectOne("SHOW CREATE TABLE `{$table}`");
            $createTableArray = (array) $createTableResult;
            $createTableSql = $createTableArray['Create Table'] ?? array_values($createTableArray)[1];

            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sql .= $createTableSql.";\n\n";

            $rows = DB::table($table)->get();

            if ($rows->isNotEmpty()) {
                foreach ($rows as $record) {
                    $recordArray = (array) $record;
                    $columns = array_map(fn (string $col): string => "`{$col}`", array_keys($recordArray));
                    $values = array_map(function (mixed $val) use ($pdo): string {
                        if ($val === null) {
                            return 'NULL';
                        }

                        return $pdo->quote((string) $val, PDO::PARAM_STR);
                    }, array_values($recordArray));

                    $sql .= sprintf(
                        "INSERT INTO `%s` (%s) VALUES (%s);\n",
                        $table,
                        implode(', ', $columns),
                        implode(', ', $values)
                    );
                }

                $sql .= "\n";
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        $path = database_path('backup.sql');
        File::put($path, $sql);

        $this->components->info("Database berhasil disimpan ke: {$path}");
        $this->components->bulletList([
            'Sekarang kamu tinggal jalankan: git add . && git commit -m "update" && git push',
        ]);

        return self::SUCCESS;
    }
}
