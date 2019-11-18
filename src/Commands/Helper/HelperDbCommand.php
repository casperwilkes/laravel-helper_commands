<?php

/**
 * Purpose:
 *  Database specific helper to run common database commands quickly.
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
 * Class HelperDbCommand
 * @package HelperCommands\Commands\Helper
 */
class HelperDbCommand extends Command {

    use HelperCommandTrait;

    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'helper:db '
                           . '{--f|fresh : Runs fresh migration}'
                           . '{--r|refresh : Refreshes migrations}'
                           . '{--w|wipe : drops all tables and views}'
                           . '{--s|seed : seeds the database}'
                           . '{--a|all : Runs database refresh and seed}';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Runs database migrations and seeds if chosen. Runs `-a` flag by default.';

    /**
     * Execute the console command.
     * @return mixed
     */
    public function handle() {
        $this->init();

        if (!in_array(true, $this->options(), true)) {
            // Check at least 1 parameter was passed //
            $this->warn('Specific parameters must be passed in order to use this tool');
        } else {
            $this->info("Start Modifying database:\n");

            // Counts blocks in code structure //
            $block_count = $this->bodyBlock('count');

            // Adjust the procs //
            $this->setProcs($block_count);
            // Make sure changes are reflective //
            $this->adjustProcs(0);

            // Run code actions //
            $this->bodyBlock('run');

            // Progress output //
            $this->finishLine();

            $this->info('Finished Modifying database');
        }
    }

    /**
     * Either counts action blocks, or runs them
     * @param string $action Action to preform [count, run]
     * @return int
     */
    private function bodyBlock(string $action = 'count'): int {
        // Original action count //
        $count = 0;

        if ($this->option('wipe')) {
            if ($action == 'run') {
                $this->callBody('db:wipe', 'Wiping');
            } else {
                $count++;
            }
        }

        // Move through options in cohesive direction //
        if ($this->option('all') || $this->option('refresh')) {
            if ($action == 'run') {
                $this->callBody('migrate:refresh', 'Refreshing');
            } else {
                $count++;
            }
        } elseif ($this->option('fresh')) {
            if ($action == 'run') {
                $this->callBody('migrate:fresh', 'Fresh');
            } else {
                $count++;
            }
        }

        // Check if we're seeding the database //
        if ($this->option('all') || $this->option('seed')) {
            if ($action == 'run') {
                // can't wipe and seed //
                $this->callBody('db:seed', 'Seeding');
            } else {
                $count++;
            }
        }

        // Count of statement blocks //
        return $count;
    }

    /**
     * Builds the body for the call to method and responses
     * @param string $cmd
     * @param string $action
     */
    private function callBody(string $cmd, string $action): void {
        $this->line("{$action} database started");

        $this->call($cmd);

        $this->bar->advance();

        $this->line("\n{$action} database finished");
    }
}
