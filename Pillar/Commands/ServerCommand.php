<?php

namespace Pillar\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ServerCommand extends Command {

    protected static $name = 'server';
    protected static $host = 'localhost';
    protected static $port = '8080';

    protected function configure() {
        $this->setName(self::$name);
        $this->setDescription('Serve Pillar via the built-in PHP webserver');
        $this->addOption('host', null, InputOption::VALUE_OPTIONAL, 'The host address to serve the application on.', self::getHost());
        $this->addOption('port', null, InputOption::VALUE_OPTIONAL, 'The port to serve the application on.', self::getPort());
    }

    protected function execute(InputInterface $input, OutputInterface $output): int  {
        $host = $input->getOption('host');
        $port = $input->getOption('port');
        $output->writeln('<info>Starting server on ' . $host . ':' . $port . '</info>');
        exec('php -S ' . $host . ':' . $port);

        // we're done now
        return Command::SUCCESS;
    }

    protected static function getHost() {
        // Get default host
        $host = self::$host;

        // Check if .env host defined
        if ($_ENV['HOST']) {
            $host = $_ENV['HOST'];
        }

        return $host;
    }

    protected static function getPort() {
        // Get default port
        $port = self::$port;

        // Check if .env port defined
        if ($_ENV['PORT']) {
            $port = $_ENV['PORT'];
        }

        return $port;
    }
}
