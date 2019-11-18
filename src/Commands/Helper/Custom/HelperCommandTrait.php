<?php

/**
 * Purpose:
 *  Service provider for laravel. Starts the setup process of the environment detector.
 * History:
 *  100919 - Wilkes: Created file
 * @author Casper Wilkes <casper@casperwilkes.net>
 * @package CasperWilkes\HelperCommands
 * @copyright 2019 - casper wilkes
 * @license MIT
 */

namespace HelperCommands\Commands\Helper\Custom;

use Illuminate\Console\Parser;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * Trait HelperCommandTrait
 * @package HelperCommands\Commands\Helper\Custom
 */
trait HelperCommandTrait {

    /**
     * Start time of execution
     * @var string
     */
    private $start_time;

    /**
     * Options included in all commands
     * @var array
     */
    private $default_options = [
        'help',
        'quiet',
        'verbose',
        'version',
        'ansi',
        'no-ansi',
        'no-interaction',
        'env',
    ];

    /**
     * Sets progress bar ticks
     * @var int
     */
    protected $procs = 0;

    /**
     * Sets the procs for arguments
     * @var int
     */
    private $argProc = 0;

    /**
     * Local options from signature
     * @var array
     */
    private $opts = [];

    /**
     * Gets the default options for further processing
     * @var array
     */
    private $opts_default = [];

    /**
     * Local arguments from signature
     * @var array
     */
    private $args = [];

    /**
     * Gets default args for further processing
     * @var array
     */
    private $args_default = [];

    /**
     * Progress bar
     * @var null|ProgressBar
     */
    private $bar = null;

    /**
     * Create a new command instance.
     * @return void
     */
    public function __construct() {
        $this->startTimer();
        parent::__construct();
    }

    /**
     * Initializes common properties
     * @param bool $progress Whether to initialize progress bar
     * @return void
     */
    protected function init(bool $progress = true): void {
        // Parse args and opts from signature //
        $this->parseSignature();
        // Hydrate args and opts //
        $this->setOptions();
        $this->setArguments();
        // Set action count //
        $this->setProcs();

        if ($progress) {
            // Setup bar //
            $this->setProgressBar();
        }
    }

    /**
     * Gets the local options. If key is passed, returns just that value
     * @param string $key Key to parse
     * @param mixed $default Default value to return if key is not found
     * @return mixed|array Single value or array of values
     */
    protected function getOptions(string $key = '', $default = null) {
        // Check that key being asked for does not exist //
        if ($key !== '' && !array_key_exists($key, $this->opts)) {
            // return the default value //
            return $default;
        }

        // Check that the key being asked for exists //
        if ($key !== '' && array_key_exists($key, $this->opts)) {
            // Return key being asked for //
            return $this->opts[$key];
        }

        // return whole array //
        return $this->opts;
    }

    /**
     * Gets the local arguments. If key is passed, returns just that value.
     * @param string $key Key to parse
     * @param mixed $default
     * @return mixed|array Single value or array of values
     */
    protected function getArguments(string $key = '', $default = null) {
        // Check that key being asked for does not exist //
        if ($key !== '' && !array_key_exists($key, $this->args)) {
            // return default //
            return $default;
        }

        // Check that the key being asked for exists //
        if ($key !== '' && array_key_exists($key, $this->args)) {
            // Get requested args //
            return $this->args[$key];
        }

        // get all args //
        return $this->args;
    }

    /**
     * Adjusts the amounts of procs
     * @param int $amount
     * @return void
     */
    protected function adjustProcs(int $amount): void {
        $this->procs += $amount;
        $this->bar->setMaxSteps($this->procs);
    }

    /**
     * Gets the procs
     * @return int
     */
    protected function getProcs(): int {
        return $this->procs;
    }

    /**
     * Runs a diagnostic total
     * @return void
     */
    protected function finishLine(): void {
        $this->comment("\n\nTotals: \n");
        $this->displayTimer();
        $this->comment('Total Progress performed');
        $this->bar->finish();
        $this->line(PHP_EOL);
    }

    /**
     * Displays an execution timer
     * @return void
     */
    protected function displayTimer(): void {
        $finish = round(microtime(true) - $this->start_time, 2);
        $this->comment("Total execution time: {$finish} seconds\n");
    }

    /**
     * Removes a file by path
     * @param string $path path to file
     * @return bool
     */
    protected function delete(string $path): bool {
        // If path exists //
        if (realpath($path) !== false) {
            $base = basename($path);

            // Remove original //
            if (unlink($path)) {
                // File deleted //
                $this->comment("Deleted: {$base}");

                return true;
            }

            // Couldn't delete file //
            $this->warn("Problem preventing deletion of: {$base}");
        }

        // File path given incorrect //
        $this->warn("Path: {$path} is invalid");

        return false;
    }

    /**
     * Compresses a file by path
     * @param string $path path to file
     * @return bool
     */
    protected function compress(string $path): bool {
        if (realpath($path) !== false) {
            $base = basename($path);

            if (copy($path, "compress.zlib://{$path}.gz")) {
                // Attempt to zip the file //
                $this->comment("Compressed: {$base}");
                $this->comment('Removing original');
                // delete original path //
                $this->delete($path);

                return true;
            }

            $this->warn("Problem preventing the compression of: {$base}");

        }

        // File path given incorrect //
        $this->warn("Path: {$path} is invalid");

        return false;
    }

    /**
     * Sets the procs for the progress bar
     * @param int|null $override Hardcode Override value for procs
     * @return void
     */
    protected function setProcs(int $override = null): void {
        if ($override !== null) {
            $procs = $override;
        } else {
            $procs = $this->procOptions() + $this->procArguments();
        }

        $this->procs = $procs;
    }

    /**
     * Starts the timer for console execution display
     */
    private function startTimer(): void {
        // Start timer //
        $this->start_time = microtime(true);
    }

    /**
     * Builds a progress bar
     */
    private function setProgressBar(): void {
        $this->bar = $this->output->createProgressBar($this->procs);
    }

    /**
     * Collects the signature, and parses it out into local options and arguments
     * @return void
     */
    private function parseSignature(): void {
        [$name, $arguments, $options] = Parser::parse($this->signature);

        /**
         * Passes in element of input, and outputs an array by reference to draw values from
         * @param Symfony\Component\Console\Input\InputArgument,| Symfony\Component\Console\Input\InputOption $arg
         */
        $column_dump = function ($arg) use (&$import) {
            $import[$arg->getName()] = $arg->getDefault();
        };

        // Set empty array to reference //
        $import = [];
        // Populate import //
        array_walk($options, $column_dump);
        // Transfer import //
        $this->opts = $this->opts_default = $import;

        // Reset import //
        $import = [];

        array_walk($arguments, $column_dump);
        $this->args = $this->args_default = $import;

        // This time unset //
        unset($import);
    }

    /**
     * Sets the local options from signature
     * @return void
     */
    private function setOptions(): void {
        // use this->opts to hydrate //
        $opts = $this->hydrateArrays($this->options(), $this->opts);

        // Check the all key exists //
        $all_exists = array_key_exists('all', $this->opts);

        // If all exists, and is true or all exists, and all options are false //
        if (($all_exists && $opts['all']) || ($all_exists && !in_array(true, $opts, true))) {
            // Fill all values with true //
            $opts = array_fill_keys(array_keys($opts), true);
        }

        // Set the opts //
        $this->opts = $opts;
    }

    /**
     *Sets the local arguments from signature
     * @return void
     */
    private function setArguments(): void {
        $this->args = $this->hydrateArrays($this->arguments(), $this->args);
    }

    /**
     * Counts the procs from options
     * @return int
     */
    private function procOptions(): int {
        // Checks if all is set //
        $minus_all = array_key_exists('all', $this->opts) ? 1 : 0;

        // Checks if any true values exist //
        $truth_check = in_array(true, $this->opts, true);

        // If all is set, remove from final count //
        $count = count($this->opts) - $minus_all;

        // If all are false, or all exists and is true //
        if (!$truth_check || ($minus_all == 1 && $this->opts['all'])) {
            return $count;
        }

        return count(array_diff_assoc($this->opts, $this->opts_default));
    }

    /**
     * Counts the procs from arguments
     * @return int
     */
    private function procArguments(): int {
        // Compare the set args against default args from @signature
        return count(array_diff_assoc($this->args, $this->args_default));
    }

    /**
     * Collects data from system array and local array to return an efficient array
     * @param array $sysArray
     * @param array $localArray
     * @return array
     */
    private function hydrateArrays(array $sysArray, array $localArray): array {
        /**
         * Checks keys existence
         * @param string $key
         * @return bool
         */
        $getValues = function ($key) use ($localArray) {
            return array_key_exists($key, $localArray);
        };

        return array_filter($sysArray, $getValues, ARRAY_FILTER_USE_KEY);
    }
}
