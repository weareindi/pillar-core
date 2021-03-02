<?php

namespace Pillar\App;

use Symfony\Component\Console\Application;
use Pillar\Commands\ExportCommand;
use Pillar\Commands\GenerateTemplateCommand;
use Pillar\Commands\HtmlCommand;
use Pillar\Commands\ServerCommand;
use Pillar\Commands\GulpCommand;

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
            new ExportCommand(),
            new GenerateTemplateCommand(),
            new HtmlCommand(),
            new ServerCommand(),
            new GulpCommand()
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
