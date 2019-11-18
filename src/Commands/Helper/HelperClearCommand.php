<?php

/**
 * Purpose:
 *  Helper command to clear out old caches/configs. Compresses logs and session files unless otherwise indicated.
 * History:
 *  100919 - Wilkes: Created file
 * @author Casper Wilkes <casper@casperwilkes.net>
 * @package CasperWilkes\HelperCommands
 * @copyright 2019 - casper wilkes
 * @license MIT
 */

namespace HelperCommands\Commands\Helper;

use HelperCommands\Commands\Helper\Custom\HelperCommandTrait;
use Illuminate\Console\Command;

/**
 * Class HelperClearCommand
 * @package HelperCommands\Commands\Helper
 */
class HelperClearCommand extends Command {

    use HelperCommandTrait;

    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'helper:clear '
                           . '{delete? : Deletes session and logs}'
                           . '{--c|cache : Dumps all the caches}'
                           . '{--d|debugger : Dumps the debugger cache}'
                           . '{--b|bootstrap : Dumps all the views}'
                           . '{--l|log : Dumps the logs}'
                           . '{--s|session : Dumps the sessions}'
                           . '{--x|compress : Compresses files}'
                           . '{--a|all : Clears all}';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Clears out cached information, compresses sessions and logs.'
                             . ' Runs `-a` flag by default. Delete must specifically be set to run.';

    /**
     * Execute the console command.
     * @return mixed
     */
    public function handle() {
        $this->init();
        // Account for compression option //
        $this->adjustProcs(-1);

        // Account for delete argument //
        if ($this->getArguments('delete') !== null) {
            $this->adjustProcs(-1);
        }

        // check we have options //
        if ($this->getProcs()) {
            $this->info("\nStart clearing files:\n");

            if ($this->getOptions('cache')) {
                $this->cache();
            }

            if ($this->getOptions('debugger')) {
                $this->debugger();
            }

            if ($this->getOptions('log')) {
                $this->logs();
            }

            if ($this->getOptions('session')) {
                $this->session();
            }

            if ($this->getOptions('bootstrap')) {
                $this->bootstrap();
            }

            $this->finishLine();

            $this->info('Finished clearing files');
        }

    }

    /**
     * Compresses the sessions
     * @return void
     */
    private function session(): void {
        $this->line('');
        $this->line("\nStart clearing sessions");

        // Check if delete is set //
        if ($this->getArguments('delete') !== null) {
            // Delete the files //
            $this->deleteFiles('sessions');
        } else {
            // Compress files //
            $this->compressFiles('sessions');
        }

        $this->info('Finished clearing sessions');
        $this->line('');
        // proc bar //
        $this->bar->advance();
    }

    /**
     * Compresses logs
     * @return void
     */
    private function logs(): void {
        $this->line('');
        $this->line("\nStart clearing logs");

        // Check if delete is set //
        if ($this->getArguments('delete') !== null) {
            // Delete the files //
            $this->deleteFiles('logs');
        } else {
            // Compress files //
            $this->compressFiles('logs');
        }

        $this->info('Finished clearing logs');
        $this->line('');

        // proc bar //
        $this->bar->advance();
    }

    /**
     * Clears cache files
     * @return void
     */
    private function cache(): void {
        $this->line('Clearing caches:');
        $this->comment('Cache:');
        $this->call('cache:clear');

        $this->comment('Config:');
        $this->call('config:clear');

        $this->comment('Compiled:');
        $this->call('clear-compiled');

        $this->comment('Route:');
        $this->call('route:clear');

        $this->comment('Views:');
        $this->call('view:clear');

        $this->info('Finished clearing caches');
        $this->line('');

        // proc bar //
        $this->bar->advance();
    }

    /**
     * Clears the debugbar cache
     * @return void
     */
    private function debugger(): void {
        $this->line('');
        $this->line("\nStart clearing debugger");

        // Delete the files //
        $this->deleteFiles('debugger');

        $this->info('Finished clearing debugger');
        $this->line('');

        // proc bar //
        $this->bar->advance();
    }

    /**
     * Clears views
     * @return void
     */
    private function bootstrap(): void {
        $this->line('');
        $this->line("\nClearing bootstrap:");

        $this->call('optimize:clear');

        $this->call('view:clear');

        $this->info('Finished clearing bootstrap');
        $this->line('');

        // proc bar //
        $this->bar->advance();
    }

    /**
     * Gets a collection of file paths
     * @param string $pathShortName Short name to search through [logs, sessions]
     * @return array|false
     */
    private function collectPaths(string $pathShortName) {
        // Correspond paths to actual paths //
        $type = [
            'logs' => $this->getArguments('delete') == null ? 'logs/*.log' : 'logs/*',
            'sessions' => 'framework/sessions/*',
            'debugger' => 'debugbar/*',
        ];

        // set search path //
        $search = $type[$pathShortName] ?? $pathShortName;

        // Get the files in the path //
        return glob(storage_path($search));
    }

    /**
     * Deletes file paths
     * @param $path
     * @return void
     */
    private function deleteFiles($path): void {
        $files = $this->collectPaths($path);

        if (empty($files)) {
            $this->warn("No {$path} to remove");
        } else {
            foreach ($files as $file) {
                //  Get the basename of the file //
                $base = basename($file);

                $this->comment("Deleting file: {$base}");

                // Remove original //
                if ($this->delete($file)) {
                    $this->comment("Deleted: {$base}");
                } else {
                    $this->warn("Could not delete: {$base}");
                }

            }
        }
    }

    /**
     * Compresses file paths
     * @param $path
     * @return void
     */
    private function compressFiles($path): void {
        // Get the files in the path //
        $files = $this->collectPaths($path);

        // Check we got some files //
        if (empty($files)) {
            $this->warn("No {$path} to compress");
        } else {
            foreach ($files as $file) {
                //  Get the basename of the file //
                $base = basename($file);

                $this->comment("Compressing file: {$base}");

                // Attempt to zip the file //
                if ($this->compress($file)) {
                    $this->comment("Compressed: {$base}");
                } else {
                    $this->warn("Could not compress: {$base}");
                }
            }
        }
    }
}
