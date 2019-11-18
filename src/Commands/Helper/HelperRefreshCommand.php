<?php

/**
 * Purpose:
 *  Helper command to quickly remove old caches/configs and build new ones.
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
 * Class HelperRefreshCommand
 * @package HelperCommands\Commands\Helper
 */
class HelperRefreshCommand extends Command {

    use HelperCommandTrait;

    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'helper:refresh '
                           . '{--c|clear : Clears previous optimizations}'
                           . '{--b|build : Builds optimizations}'
                           . '{--a|all : Builds and clears all}';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Clears out previous optimizations and rebuilds new ones. Runs `-a` flag by default.';

    /**
     * Execute the console command.
     * @return mixed
     */
    public function handle() {
        $this->init();

        if ($this->getProcs()) {
            $this->info("Start running optimizations:\n");

            if ($this->getOptions('clear')) {
                $this->clear();
            }

            if ($this->getOptions('build')) {
                $this->build();
            }

            $this->finishLine();

            $this->info("Finish running optimizations:\n");
        }
    }

    /**
     * Clears optimizations
     * @return void
     */
    private function clear(): void {
        $this->line('Start clearing optimizations');

        // build files //
        $this->call('helper:clear');

        $this->info('Finished clearing optimizations');
        $this->line('');

        // proc bar //
        $this->bar->advance();
    }

    /**
     * Builds optimizations
     * @return void
     */
    private function build(): void {
        $this->line('');
        $this->line('Start building optimizations');

        // build files //
        $this->call('helper:build');

        $this->info('Finished building optimizations');
        $this->line('');

        // proc bar //
        $this->bar->advance();
    }
}
