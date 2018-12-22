<?php

namespace PillarCore\App;

use Symfony\Component\Console\Application;
use PillarCore\Commands\ServerCommand;

/**
 * Pillar Core Console
 */
class Console {

    protected static $console;

    protected static $commands;

    /**
     * @param array $userCommands An array of custom console commands
     */
    function __construct(Array $userCommands = []) {
        // Define default commands and merge with user defined commands
        self::$commands = array_merge([
            new ServerCommand()
        ], $userCommands);

        self::registerConsole();
        self::registerCommands();
        self::runConsole();
    }

    /**
     * Register console application
     */
    protected static function registerConsole() {
        self::$console = new Application();
    }

    /**
     * Register all defined commands
     */
    protected static function registerCommands() {
        foreach (self::$commands as $command) {
            self::$console->add($command);
        }
    }

    /**
     * Run the console application
     */
    protected static function runConsole() {
        self::$console->run();
    }
}
