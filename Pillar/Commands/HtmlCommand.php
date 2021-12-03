<?php

namespace Pillar\Commands;

use Pillar\App\Paths;
use Pillar\Controllers\PageController;
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
        $output->writeln('<info>EXPORTING HTML (From: ' . PAGES . ' to ' . $export_html_dest . ')</info>');
        $output->writeln('SRC: ' . PAGES);
        $output->writeln('DEST: ' . $export_html_dest);
        self::export(PAGES, $export_html_dest, true, $output);
        $output->writeln('<info>HTML EXPORT COMPLETE</info>');

        // we're done now
        return Command::SUCCESS;
    }

    /**
     * Export
     * @param  string          $src        An absolute path
     * @param  string          $dest       An absolute path
     * @param  bool            $is_library Are we exporting our library?
     * @param  OutputInterface $output
     */
    protected static function export(string $src, string $dest, bool $is_library, OutputInterface $output) {
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

        foreach ($ii as $file) {
            // If it's not a file, we can skip to the next iteration
            if (!$file->isFile()) {
                continue;
            }

            // Get destination directory
            $destinationDir = dirname($dest . substr($file->getPath(), strlen($src)));

            // Get destination filename
            $destinationFilename = substr($file->getPath(), strrpos($file->getPath(), '/') + 1) . '.html';

            // Get HTML as string
            $html = PageController::html(dirname($file->getPathname()));

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
     * @param string $directory The directory to be emptied
     */
    protected static function deleteFiles(string $directory) {
        $di = new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS);
        $ii = new \RecursiveIteratorIterator($di, \RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($ii as $file) {
            self::deleteItem($file);
        }
    }

    /**
     * Delete File
     * @param object $file Absolute path to file or emapy directory
     */
    protected static function deleteItem(object $file) {
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
     * @param  string $destinationDir
     * @param  string $destinationFilename
     * @param  string $file
     */
    public static function saveHtml(string $destinationDir, string $destinationFilename, string $html) {
        if (!file_exists($destinationDir)) {
            mkdir($destinationDir, 0777, true);
        }

        if (!file_put_contents(($destinationDir . '/' . $destinationFilename), $html)) {
            return false;
        }

        return true;
    }
}
