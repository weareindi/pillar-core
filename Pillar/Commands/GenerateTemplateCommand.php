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

    protected function execute(InputInterface $input, OutputInterface $output): int  {
        $io = new SymfonyStyle($input, $output);

        // Get Type
        if ($input->getOption('page') && $input->getOption('pattern')) {
            $type = $io->choice('What library type does this new pattern belong to?', ['pages', 'patterns'], 'patterns');
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

        //
        $defined_files = getenv('GENERATE_FILES');
        if (!empty($defined_files)) {
            $files = explode('|', $defined_files);
        }

        // get destination structure
        // used for getting pattern type and name
        $destination_structure = explode('/', substr($destination, strlen(PATTERNS . '/')));

        // Create files
        foreach ($files as $file) {
            // populate full filename
            $filename = $destination . '/' . $file;

            // create template
            if ($this->createTemplate($filename)) {

                // populate template
                $this->populateTemplate(
                    $filename,
                    $file,
                    $type,
                    $destination_structure[0],
                    $destination_structure[1]
                );
            };
        }

        $output->writeln('<info>Template generated</info>');

        // we're done now
        return Command::SUCCESS;
    }

    /**
     * Create the main template file
     * @param  string $filename
     * @return [type]           [description]
     */
    protected function createTemplate(string $filename) {
        return touch($filename);
    }

    /**
     * Populate Template
     * Look for and use user-defined boiler plates in the Pillar/App/TemplateBoilerplates/ directory
     * @param  string $filename
     * @param  string $file
     * @param  string $type
     * @param  string $pattern_parent
     * @param  string $pattern_name
     * @return
     */
    protected function populateTemplate(string $filename, string $file, string $type, string $pattern_parent, string $pattern_name) {
        // skip to next iteration if page pattern type
        if ($type === 'pages') {
            return false;
        }

        // prepare boilerplate path
        $boilerlate_path = ROOT . '/App/TemplateBoilerplates/' . $file;

        // do we have a boilerplate to copy in?
        if (!file_exists($boilerlate_path)) {
            return false;
        }

        // pattern type
        $pattern_type = substr($pattern_parent, 0, 1);

        // get boilerplate_contents
        $boilerplate_contents = file_get_contents($boilerlate_path);

        // replace variables
        $boilerplate_contents = str_replace(
            ['$1', '$2'],
            [$pattern_type, $pattern_name],
            $boilerplate_contents
        );

        // save updated contents to file
        return file_put_contents($filename, $boilerplate_contents);
    }
}
