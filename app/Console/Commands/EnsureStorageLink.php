<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class EnsureStorageLink extends Command
{
    protected $signature = 'storage:ensure-link';

    protected $description = 'Create the public/storage symlink if it is missing (safe to run on every install/update)';

    public function handle(): int
    {
        if (file_exists(public_path('storage'))) {
            $this->info('Storage link already exists.');

            return self::SUCCESS;
        }

        return $this->call('storage:link');
    }
}
