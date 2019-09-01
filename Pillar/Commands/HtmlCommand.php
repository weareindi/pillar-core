<?php

namespace Pillar\Commands;

use Pillar\App\Paths;
use Pillar\Controllers\PagesController;
use Pillar\Twig\TwigService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class HtmlCommand extends Command {

    protected static $name = 'html';

    protected function configure() {
        $this->setName(self::$name);
		$this->setDescription('Export your pages to html');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        // Define the default paths
        Paths::define();

        // Register the Twig service so we can compile
        TwigService::register();

        // Check if html destination directory defined in .env
        $export_html_dest = getenv('EXPORT_HTML_DEST');
        if (!$export_html_dest || empty($export_html_dest)) {
            return $output->writeln('<error>EXPORT_HTML_DEST is not defined in your .env</error>');
        }
        $output->writeln('<info>EXPORTING HTML (From: '.PAGES.' to '.$export_html_dest.')</info>');
        $output->writeln('SRC: ' . PAGES);
        $output->writeln('DEST: ' . $export_html_dest);
        self::export(PAGES, $export_html_dest, true, $output);
        $output->writeln('<info>HTML EXPORT COMPLETE</info>');
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

            // Get destination directory
            $destinationDir = dirname($dest . substr($file->getPath(), strlen($src)));

            // Get destination filename
            $destinationFilename = substr($file->getPath(), strrpos($file->getPath(), '/') + 1) . '.html';

            // Get HTML as string
            $html = PagesController::html(dirname($file->getPathname()));

            // Save file to destination
            if (!self::saveHtml($destinationDir, $destinationFilename, $html)) {
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
    public static function saveHtml(String $destinationDir, String $destinationFilename, String $html) {
        if (!file_exists($destinationDir)) {
            mkdir($destinationDir, 0777, true);
        }

        if (!file_put_contents(($destinationDir . '/' . $destinationFilename), $html)) {
            return false;
        }

        return true;
    }
}
