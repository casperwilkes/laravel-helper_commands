# Laravel Helper Commands #

This is a package of simple artisan helper commands to help with the ease of development and some deployment of laravel.
It also gives you the ability to write your own helper commands.

After installing this package, a new namespace will appear under `artisan list` as `helper`, and `make`. New commands built with this package will appear
under `helper` by default, unless otherwise specified by command creator. 

TOC
* [Installation](#installation)
* [Usage](#usage)
    * [Clear](#clear)
    * [Build](#build)
    * [Refresh](#refresh)
    * [DB](#db)
* [Build Yor Own](#building-your-own-helpers)
    * [Command Helper](#command-helper)
* [Helper Trait](#helper-trait)
    * [Function List](#function-list)
* [Publishing](#publish)

# Installation #

There are a couple steps necessary to get the environment detector up and running.

## Composer ##

To install the package through composer:

```
composer require casperwilkes/laravel-helper_commands
```

# Usage #

Included with the base package are a couple commands at your disposal.

* [`clear`](#clear)
    * This command is to clear out caches and configs
* [`build`](#build)
    * This command builds the caches and configs
* [`refresh`](#refresh)
	* This command will clear out caches and configs, then rebuild them
* [`db`](#refresh)
	* This command is useful for quickly dumping and re-seeding your database
* [`make:command-helper`](#command-helper)
    * This command will build out a new helper

[Back To Top ^](#laravel-helper-commands)

## Clear ##

The clear command will help you do several things. It's biggest use is clearing away old config files and old caches.

### Options ###

If you run the command without any arguments, the default will run.

* `(-c | --cache)`
    * Clears the cache file
* `{-d | --debug}`
    * Dumps the debugbar cache (if it exists)
* `{-b | --bootstrap}`
    * Dumps all the optimized views and bootstrap cache
* `{-l | --log}`
    * Compresses or deletes log files
* `{-s | --session}`
    * Compesses or deletes current sessions
* `{-x | --compress}`: [Default]
    * Compress option
* `{-a | --all}` : [Default]
    * Runs `-cdblsx`

### Arguments ###

* `{delete}`: [Optional]
    * Used to delete instead of compress

### Example Usage ###

* To clear all the caches/configs:
```
$ php artisan helper:clear
```
* To clear all caches/configs + delete logs and sessions
```
$ php artisan helper:clear delete
```
* To clear just the bootstrap & cache
```
$ php artisan helper:clear -bc
```

[Back To Top ^](#laravel-helper-commands)

## Build ##

The build command is good for releasing, or seeing how the system works with all the caches in place.

### Options ###

If you run the command without any arguments, the default will run.

* `{-c| --cache}`
    * Builds all the cache files for the system
* `{-r| --route}`
    * Builds all the route cache files
* `{-b| --bootstrap}`
    * Builds bootstraps (Optimizes)
* `{-a| --all}`: [Default]
    * Runs `-crb`
### Arguments ###

* None

### Example Usage ###

* To build the route cache:
```
$ php artisan helper:build -r
```
* To build route and cache, but not bootstrap:
```
$ php artisan helper:build -cr
```

[Back To Top ^](#laravel-helper-commands)

## Refresh ##

The refresh command runs a combination of [Build](#build) and [Clear](#clear) commands to quickly refresh your current environment.

### Options ###

* `{-a | --all}`: [Default]
    * Runs `-bc`
* `{-b | --build}`
    * Builds and clears all caches/configs 
* `{-c | --clear}`
    * Clears previous caches/configs

### Arguments ###

* None

### Example Usage ###

* To clear and rebuild caches/configs:
```
$ php artisan helper:refresh
```
* To clear just the configs:
```
$ php artisan helper:refresh -c
```

[Back To Top ^](#laravel-helper-commands)

## DB ##

The db command runs a combination of `db` commands.

**Note:**

* The commands run on simple options, if more advanced usage is necessary, use the default artisan`db` command.
* Because of the sensitivity of this helper, unlike the other helpers, you must specify an option to run it

### Options ###

* `{-f | --fresh}`
    * Runs fresh migrations
* `{-r | --refresh}`
    * Refreshes the database and migrations
* `{-w | --wipe}`
    * Wipes the database of tables
* `{-s | --seed}`
    * Seeds the database with all seeds
* `{-a | --all}`:
    * Runs `-rs`

### Arguments ###

* None

### Example Usage ###

* To run a fresh migration and seed:
```
$ php artisan helper:db -fs
```
* To refresh database and run seeds:
```
$ php artsian helper:db -rs
```
* To wipe the database, run fresh migrations, and seed
```
$ php artisan helper:db -wfs
```

[Back To Top ^](#laravel-helper-commands)

# Building Your Own Helpers #

Included in this package, is the ability to create your own helper commands easily.

Building your own helpers is incredibly easy. A Helper command is used to create a new helper class ready for you to build.

**Note:** 
* This helper is contained within the Artisan `make` domain instead of the `helper` domain. 

## Command-Helper ##

### Options ###

* none

### Arguments ###

* `{name}`: [Required]
    * Name of the class. Best practice would be `HelperCamelCaseNameCommand`
    
### Example Usage ###

To make a new helper, run:

```
$ php artisan make:command-helper HelperTestCommand
```

[Back To Top ^](#laravel-helper-commands)

### Custom Helpers ###

The `make:command-helper` command will generate a new, ready to populate, command within the `./app/Console/Helper` directory.

The default signature is:

```php
protected $signature = 'helper:new-command';
```

Just update this signature like you would any other command, replacing `new-command` with your command name. With this implementation, there is no 
need to register your new command with the `Kernel.php`, it should be loaded automatically. 

If the new Helper does not get loaded auto-magically, you'll need to update your autoloader classes in composer. 

Run the following command to rebuild the class cache:

```
$ php composer dumpautoload
```

When your new class is created, it's automatically available in artisan too. So if you type `php artisan list`, under the namespace `helper`, you'll 
see you new command waiting:

```php
 helper
  helper:build           Builds necessary caches for optimization. Runs `-a` flag by default.
  helper:clear           Clears out cached information, compresses sessions and logs. Runs `-a` flag by default. Delete must specifically be set to run.
  helper:db              Runs database migrations and seeds if chosen. Runs `-a` flag by default.
> helper:new-command     Command description
> helper:test            This is a test command
  helper:refresh         Clears out previous optimizations and rebuilds new ones. Runs `-a` flag by default.
 key
  key:generate           Set the application key

``` 

Also, when this class is created, it automatically initializes the [`HelperCommandTrait`](#helper-trait) within the commands `handle` method.

The helper trait comes with some useful tools for quickly building out your own helper commands.

[Back To Top ^](#laravel-helper-commands)

# Helper Trait #

The helper trait is a trait that comes with useful functions to help you build out your custom helper easily. When a new helper command is
generated, the `init` method is automatically populated within the `handle`. method. 

This initialize method handles a variety of different functionality to help start up your development and execution.  

## Function List ##

These are the default functions in the `HelperCommandTrait`

* [`init`](#init)
* [`getOptions`](#getoptions)
* [`getArguments`](#getarguments)
* [`adjustProcs`](#adjustprocs)
* [`getProcs`](#getprocs)
* [`finishLine`](#finishline)
* [`displayTimer`](#displaytimer)
* [`delete`](#delete)
* [`compress`](#compress)
* [`setProcs`](#setprocs)

-----

### Init ###
Init will: 

* parse the signature line to grab arguments and options
* Hydrates the arguments array with incoming values
* Hydrates the options with array with incoming values
* Set the action count
* If `$progress` is passed, it will initialize a new progress bar

```php
/**
 * Initializes common properties
 * @param bool $progress Whether to initialize progress bar
 * @return void
 */
protected function init(bool $progress = true): void {}
```

[Back to Function List ^](#function-list)

-----

### getOptions ###

This function is a mixed bag. 

* If nothing is passed, it will return all options (just the ones you specified), and removes the "default" options.
* If an option name is passed, it will return that option's value
* If an option name is passed, and the value does not exist, it will return the default value, or `null` if nothing is passed 

```php
/**
 * Gets the local options. If key is passed, returns just that value
 * @param string $key Key to parse
 * @param mixed $default Default value to return if key is not found
 * @return mixed|array Single value or array of values
 */
protected function getOptions(string $key = '', $default = null) {}
```

[Back to Function List ^](#function-list)

-----

### getArguments ###

Like `getOptions`, this function is a mixed bag.

* If nothing is passed, it will return all arguments
* If an argument name is passed, it will return that argument's value
* If an argument name is passed, and the value does not exist, it will return the default value, or `null` if nothing is passed 

```php
/**
 * Gets the local arguments. If key is passed, returns just that value.
 * @param string $key Key to parse
 * @param mixed $default
 * @return mixed|array Single value or array of values
 */
protected function getArguments(string $key = '', $default = null) {}
```

[Back to Function List ^](#function-list)

-----

### adjustProcs ###

This method is for adjusting procs (actions) for the progress bar. It will allow you to deduct or add procs to your initialized bar.

The procs are set after the options and arguments are parsed from the signature. For options that are true, a proc is added to the procs property.
If you want to run extra processing, and let the user know, without having to ask for more information, you can adjust the proc with this method.

So, if you've got 3 options in your signature, and 2 are returned as true, then you'll have 2 procs. 

But, if you want to adjust your output to run extra processing, and have the progress bar pick it up. You could use `$this->adjustProcs(2)`, this 
will add 2 extra procs to the progress bar. 

If you want to remove a proc from the progres bar, you would use `$this->adjustProcs(-2)`. 

```php
/**
 * Adjusts the amounts of procs
 * @param int $amount
 * @return void
 */
protected function adjustProcs(int $amount): void {}
```

[Back to Function List ^](#function-list)

-----

### getProcs ###

This method returns the current amount of procs (actions) that are to run.

```php
/**
 * Gets the procs
 * @return int
 */
protected function getProcs(): int {}
```

[Back to Function List ^](#function-list)

-----

### finishLine ###

This is purely and output function to display the total amount of execution time the command took, and the status of the progress bar

```php
/**
 * Runs a diagnostic total
 * @return void
 */
protected function finishLine(): void {}
```

[Back to Function List ^](#function-list)

------

### displayTimer ###

This method will display the amount of time the command execution has taken. You can use this by itself if you don't wish to use the progress bar.

```php
/**
 * Displays an execution timer
 * @return void
 */
protected function displayTimer(): void {}
```

[Back to Function List ^](#function-list)

-----

### delete ###

This function will take a file path, and remove that file from the filesystem.

```php
/**
 * Removes a file by path
 * @param string $path path to file
 * @return bool
 */
protected function delete(string $path): bool {}
```

[Back to Function List ^](#function-list)

------

### compress ###

This method will take a file path, and compress that file within the filesystem.

```php
/**
 * Compresses a file by path
 * @param string $path path to file
 * @return bool
 */
protected function compress(string $path): bool {}
```

[Back to Function List ^](#function-list)

-----

### setProcs ###

This function sets the initial procs for the command. You can hard-code the amount of actions directly with this method.

```php
/**
 * Sets the procs for the progress bar
 * @param int|null $override Hardcode Override value for procs
 * @return void
 */
protected function setProcs(int $override = null): void {}
```

[Back to Function List ^](#function-list)

------

### Bar Advance ###

This is progress bar native function. Because we initialize the property bar as a progress bar, we have access to it's methods.

To advance the bar, call this within the command 

```php
$this->bar->advance();
```

[Back to Function List ^](#function-list)

## Adding Your Own Methods ##

In order to add your own methods to the `HelperCommandTrait`, you will need to [publish](#laravel-publish) the vendor assets.

[Back To Top ^](#laravel-helper-commands)

# Publish #

Publishing the `HelperCommandTrait` is easy. Run the following artisan command to get the trait published to the `./app/Console/Helper/Custom` directory.

## Publish Command ##

Publish using the tag:
```
php artisan vendor:publish --tag=helper-commands
```

After the trait is published, you may need to dump the composer autoloader and clear your caches:

```
$ php composer dumpautoload
$ php artisan helper:clear
```

[Back To Top ^](#laravel-helper-commands)