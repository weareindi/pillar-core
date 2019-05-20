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

    protected function execute(InputInterface $input, OutputInterface $output) {
		$host = $input->getOption('host');
		$port = $input->getOption('port');
		$output->writeln('<info>Starting server on ' . $host . ':' . $port . '</info>');
		exec('php -S ' . $host . ':' . $port);
    }

    protected function getHost() {
        // Get default host
        $host = self::$host;

        // Check if .env host defined
        if (getenv('HOST')) {
            $host = getenv('HOST');
        }

        return $host;
    }

    protected function getPort() {
        // Get default port
        $port = self::$port;

        // Check if .env port defined
        if (getenv('PORT')) {
            $port = getenv('PORT');
        }

        return $port;
    }
}
