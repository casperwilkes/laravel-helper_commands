<?php

/**
 * Purpose:
 *  Helper command to build caches/configs
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
 * Class HelperBuildCommand
 * @package HelperCommands\Commands\Helper
 */
class HelperBuildCommand extends Command {

    use HelperCommandTrait;

    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'helper:build '
                           . '{--c|cache : Builds the caches}'
                           . '{--r|route : Builds routes}'
                           . '{--b|bootstrap : Builds bootstraps}'
                           . '{--a|all : Builds all}';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Builds necessary caches for optimization. Runs `-a` flag by default.';

    /**
     * Execute the console command.
     * @return mixed
     */
    public function handle() {
        $this->init();

        // we have stuff to do //
        if ($this->getProcs()) {
            $this->info("Start building files:\n");

            if ($this->getOptions('cache')) {
                $this->caches();
            }
            if ($this->getOptions('route')) {
                $this->routes();
            }
            if ($this->getOptions('bootstrap')) {
                $this->bootstrap();
            }

            $this->finishLine();

            $this->info('Finished building files');
        }
    }

    /**
     * Builds the cache files
     * @return void
     */
    private function caches(): void {
        $this->line('Start building caches');

        // build files //
        $this->call('config:cache');

        $this->info('Finished building caches');
        $this->line('');

        // proc bar //
        $this->bar->advance();
    }

    /**
     * Builds the route files
     * @return void
     */
    private function routes(): void {
        $this->line('');
        $this->line('Start building routes');

        // build files //
        $this->call('route:cache');

        $this->info('Finished building routes');
        $this->line('');

        // proc bar //
        $this->bar->advance();
    }

    /**
     * Builds the view files
     * @return void
     */
    private function bootstrap(): void {
        $this->line('');
        $this->line('Start building bootstrap');

        // build files //
        $this->call('optimize');

        $this->info('Finished building bootstrap');
        $this->line('');

        // proc bar //
        $this->bar->advance();
    }
}
