<?php

namespace Pillar\Commands;

use Pillar\App\Paths;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExportCommand extends Command {

    protected static $name = 'export';

    protected function configure() {
        $this->setName(self::$name);
		$this->setDescription('Export your library for external use');
        $this->addOption('library', null, InputOption::VALUE_NONE, 'Export library directory to defined location. This only exports template files.');
		$this->addOption('assets', null, InputOption::VALUE_NONE, 'Export assets directory');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $export_library = $input->getOption('library');
        $export_assets = $input->getOption('assets');

        // If no specific export option defined, we'll export library as default
        if (!$export_library && !$export_assets) {
            $export_library = true;
        }

        // Check if library destination directory defined in .env
        $export_library_dest = getenv('EXPORT_LIBRARY_DEST');
        if ($export_library && (!$export_library_dest || empty($export_library_dest))) {
            return $output->writeln('<error>EXPORT_LIBRARY_DEST is not defined in your .env</error>');
        }

        // Check if assets source and destination directories defined in .env
        $export_assets_src = getenv('EXPORT_ASSETS_SRC');
        $export_assets_dest = getenv('EXPORT_ASSETS_DEST');
        if ($export_assets) {
            if (!$export_assets_src || empty($export_assets_src)) {
                return $output->writeln('<error>EXPORT_ASSETS_SRC is not defined in your .env</error>');
            }

            if (!$export_assets_dest || empty($export_assets_dest)) {
                return $output->writeln('<error>EXPORT_ASSETS_DEST is not defined in your .env</error>');
            }
        }

        if ($export_library) {
            Paths::define();
            $output->writeln('<info>EXPORTING LIBRARY (From: '.LIBRARY.' to '.$export_library_dest.')</info>');
            $output->writeln('SRC: ' . LIBRARY);
            $output->writeln('DEST: ' . $export_library_dest);
            self::export(LIBRARY, $export_library_dest, true, $output);
            $output->writeln('<info>LIBRARY EXPORT COMPLETE</info>');
        }

        if ($export_library && $export_assets) {
            $output->writeln('');
        }

        if ($export_assets) {
        	$output->writeln('<info>COPYING ASSETS DIRECTORY (From: ' . $export_assets_src . ' to ' . $export_assets_dest . ')</info>');
            $output->writeln('SRC: ' . $export_assets_src);
            $output->writeln('DEST: ' . $export_assets_dest);
            self::export($export_assets_src, $export_assets_dest, false, $output);
            $output->writeln('<info>ASSETS DIRECTORY COPY COMPLETE</info>');
        }
    }

    /**
     * Export
     * @param  String          $src        An absolute path
     * @param  String          $dest       An absolute path
     * @param  Bool            $is_library Are we exporting our library?
     * @param  OutputInterface $output
     */
    protected function export(String $src, String $dest, Bool $is_library, OutputInterface $output) {
        // Create destination directory if required
        if (!file_exists($dest)) {
            mkdir($dest, 0777, true);
        }

        // Empty the destination directory
        if (!empty($dest) && is_dir($dest)) {
            self::deleteFiles($dest);
        }

        // Loop over source files
        $di = new \RecursiveDirectoryIterator($src, \RecursiveDirectoryIterator::SKIP_DOTS);
        $ii = new \RecursiveIteratorIterator($di, \RecursiveIteratorIterator::SELF_FIRST);

        foreach($ii as $file) {
            // If it's not a file, we can skip to the next iteration
            if (!$file->isFile()) {
                continue;
            }

            // Do we want to clean the output to only include twig files ?
            if ($is_library && !in_array(pathinfo($file, PATHINFO_EXTENSION), ['twig'])) {
                continue;
            }

            // Get destination directory
            $destinationDir = $dest . substr($file->getPath(), strlen($src));

            // Get destination filename
            $destinationFilename = $file->getFilename();

            // Save file to destination
            if (!self::saveFile($destinationDir, $destinationFilename, $file->getPathname())) {
                return $output->writeln('<error>Save failed!</error>');
            }

            $output->write('<info>.</info>');
        }

        return $output->writeln('');
    }

    /**
     * Delete Files
     * @param String $directory The directory to be emptied
     */
    protected static function deleteFiles(String $directory)
    {
        $di = new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS);
        $ii = new \RecursiveIteratorIterator($di, \RecursiveIteratorIterator::CHILD_FIRST);

        foreach($ii as $file) {
            self::deleteItem($file);
        }
    }

    /**
     * Delete File
     * @param String $file Absolute path to file or emapy directory
     */
    protected static function deleteItem($file) {
        if ($file->isDir()) {
            if (@!rmdir($file->getRealPath())) {
                self::deleteItem($file);
            }
        } else {
            if (@!unlink($file->getRealPath())) {
                self::deleteItem($file);
            }
        }
    }

    /**
     * Save file to destination
     * @param  String $destinationDir
     * @param  String $destinationFilename
     * @param  String $file
     */
    public static function saveFile(String $destinationDir, String $destinationFilename, String $file) {
        if (!file_exists($destinationDir)) {
            mkdir($destinationDir, 0777, true);
        }

        if (!copy($file, ($destinationDir . '/' . $destinationFilename))) {
            return false;
        }

        return true;
    }
}
