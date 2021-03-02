<?php

namespace Pillar\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

class GulpCommand extends Command {

    protected static $name = 'gulp';

    protected function configure() {
        $this->setName(self::$name);
		$this->setDescription('Install weareindi/pillar-gulp package');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        // init IO
        $io = new SymfonyStyle($input, $output);

        // output title
        $io->title('Pillar Gulp Installation');

        // output warning
        $io->warning('This should only be applied at the start of your project as it replaces key files.');

        // output choice
        $confirm = $io->confirm('Are you sure you want to add Gulp and all related dependencies to Pillar?', false);

        // exit if not confirmed
        if (!$confirm) {
            exit;
        }

        // Check repository exists
        $dir_pillargulp = getcwd() . '/vendor/weareindi/pillar-gulp';
        if (!file_exists($dir_pillargulp)) {
            $io->error($dir_pillargulp . ' does not exist');
            exit;
        }

        // Progress Start with 3 steps
        $io->progressStart(3);

        // Init filesystem
        $filesystem = new Filesystem();

        // Copy Files to Root
        $ignore_list = [
            'composer.json',
            '.gitignore',
            'README.md'
        ];

        // Store ignore list files in memory
        $ignore_data = [];
        foreach ($ignore_list as $ignore_item) {
            $ignore_data[$ignore_item] = file_get_contents(getcwd() . '/' . $ignore_item);
        }

        // advance cli
        $io->progressAdvance(1);

        // clone gulp repo into root
        $filesystem->mirror($dir_pillargulp, getcwd(), null, ['override' => true]);

        // advance cli
        $io->progressAdvance(1);

        // copy contents from ignored data back into position
        foreach ($ignore_list as $ignore_item) {
            @file_put_contents(getcwd() . '/' . $ignore_item, $ignore_data[$ignore_item]);
        }

        // advance cli
        $io->progressAdvance(1);

        // Finish
        $io->progressFinish();
        $io->success([
            'Gulp filesystem installation complete.'
        ]);

        //
        $confirm = $io->confirm('Would you like to execute \'npm install\' now?', false);

        // exit if not confirmed
        if (!$confirm) {
            $io->info([
                'You will need to run \'npm install\' manually to install the required build tools.',
                'Please read the Gulp documentation: https://gulpjs.com/',
            ]);
            exit;
        }

        exec('npm install');

        // we're done now
        return Command::SUCCESS;
    }
}
