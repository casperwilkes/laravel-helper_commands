<?php

/**
 * Purpose:
 *  Helper command to make new helper commands
 * History:
 *  100919 - Wilkes: Created file
 * @author Casper Wilkes <casper@casperwilkes.net>
 * @package CasperWilkes\HelperCommands
 * @copyright 2019 - casper wilkes
 * @license MIT
 */

namespace HelperCommands\Commands\Helper;

use Illuminate\Console\GeneratorCommand;

/**
 * Class HelperMakeHelper
 * @package HelperCommands\Commands\Helper
 */
class HelperMakeHelper extends GeneratorCommand {

    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'make:command-helper {name}';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Create a new Helper Command';

    /**
     * Type generated
     * @var string
     */
    protected $type = 'Helper Command';

    /**
     * Get the stub file for the generator.
     * @return string
     */
    protected function getStub(): string {
        return realpath(dirname(__DIR__, 2) . '/Stubs/Command.stub');
    }

    /**
     * Get the default namespace for the class.
     * @param string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace): string {
        // Direct creation directory //
        return $rootNamespace . '\Console\Commands\Helper';
    }
}