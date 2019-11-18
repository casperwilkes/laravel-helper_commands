<?php
/**
 * Purpose:
 *  Service provider for laravel. Registers functions and publishes assets
 * History:
 *  100919 - Wilkes: Created file
 * @author Casper Wilkes <casper@casperwilkes.net>
 * @package CasperWilkes\HelperCommands
 * @copyright 2019 - casper wilkes
 * @license MIT
 */

namespace HelperCommands;

use HelperCommands\Commands\Helper\{HelperBuildCommand, HelperClearCommand, HelperDbCommand, HelperMakeHelper, HelperRefreshCommand};
use Illuminate\Support\ServiceProvider;

/**
 * Class HelperCommandServiceProvider
 * @package HelperCommands
 */
class HelperCommandServiceProvider extends ServiceProvider {

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void {
        // Publish the custom trait //
        $this->publishes(
            [
                __DIR__ . '/Commands/Helper/Custom/' => app_path('Console/Commands/Helper/Custom'),
            ]
            , 'helper-commands');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void {
        // Commands for package //
        $this->commands(
            [
                HelperBuildCommand::class,
                HelperClearCommand::class,
                HelperRefreshCommand::class,
                HelperDbCommand::class,
                HelperMakeHelper::class,
            ]
        );
    }
}
