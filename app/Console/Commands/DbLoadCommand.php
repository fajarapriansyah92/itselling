<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

#[Signature('db:load')]
#[Description('Import isi database dari database/backup.sql ke MySQL lokal')]
class DbLoadCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $path = database_path('backup.sql');

        if (! File::exists($path)) {
            $this->components->error("File {$path} belum ada. Jalankan 'php artisan db:save' di perangkat asal terlebih dahulu.");

            return self::FAILURE;
        }

        $sql = File::get($path);

        DB::unprepared($sql);

        $this->components->info('Database berhasil di-load dari database/backup.sql! Semua data sudah sama persis.');

        return self::SUCCESS;
    }
}
