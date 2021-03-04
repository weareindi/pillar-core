<?php

namespace Pillar\Commands;

use Pillar\App\Paths;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use FilesystemIterator;

class GenerateTemplateCommand extends Command {

    protected static $name = 'generate';

    protected static $shortnames = ['g', 'gen', 'make'];

    protected function configure() {
        $this->setName(self::$name);
        $this->setAliases(self::$shortnames);
		$this->setDescription('Generate a pattern');
        $this->addOption('page', null, InputOption::VALUE_NONE, 'Include this option for page template type');
        $this->addOption('pattern', null, InputOption::VALUE_NONE, 'Include this option for pattern template type');
        $this->addArgument('name', InputArgument::REQUIRED, 'The name of the pattern. eg. "components/button"');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $io = new SymfonyStyle($input, $output);

        // Get Type
        if ($input->getOption('page') && $input->getOption('pattern')) {
            $io->error('Too many library types defined');
            die();
        }

        if (!$input->getOption('page') && !$input->getOption('pattern')) {
            $type = $io->choice('What library type does this new pattern belong to?', ['pages', 'patterns'], 'patterns');
        }

        if ($input->getOption('page')) {
            $type = 'pages';
        }

        if ($input->getOption('pattern')) {
            $type = 'patterns';
        }

        if (!isset($type) || empty($type)) {
            $io->error('Library type not defined');
            die();
        }

        // Build target destination
        Paths::define();
        $destination = LIBRARY . '/' . $type . '/' . $input->getArgument('name');

        if (!file_exists($destination)) {
            mkdir($destination, 0777, true);
        }

        // If $destination exists, is it empty and ready to populate?
        if ((new FilesystemIterator($destination))->valid()) {
            $io->error('Destination not empty');
            die();
        }

        // Prepare required files
        $files = [
            'template.twig',
            'data.json'
        ];

        $defined_files = getenv('GENERATE_FILES');
        if (!empty($defined_files)) {
            $files = explode('|', $defined_files);
        }

        // Create files
        foreach ($files as $file) {
            touch($destination . '/' . $file);
        }

        $output->writeln('<info>Template generated</info>');

        // we're done now
        return Command::SUCCESS;
    }
}
