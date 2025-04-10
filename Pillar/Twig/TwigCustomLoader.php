<?php

namespace Pillar\Twig;

use Twig;
use Twig\Error\LoaderError;
use Twig\Source;

/**
 * A custom loader for Twig
 *
 * We're assuming the filename for each template is 'template.twig'.
 * Using this loader we don't need to add 'template.twig' to our Twig includes
 */
class TwigCustomLoader implements Twig\Loader\LoaderInterface {
    /**
     * Modified normalizeName to include template filename extension
     * @param  string $name
     * @return string $name
     */
    private function normalizeName(string $name): string {
        $name = preg_replace('#/{2,}#', '/', str_replace('\\', '/', $name));
        $name = $this->appendTemplateFilename($name);

        return preg_replace('#/{2,}#', '/', str_replace('\\', '/', $name));
    }

    /**
     * Append pattern name with our expected file extension
     * @param  string $name
     * @return string $name
     */
    private function appendTemplateFilename($name) {
        if ('template.twig' !== substr($name, -strlen('template.twig'))) {
            return $name . '/template.twig';
        }

        return $name;
    }

    //
    //
    //
    // EVERYTHING BELOW THIS LINE IS UNMODIFIED
    // Original found in Twig package /twig/src/Loader/FilesystemLoader.php
    //
    //
    //

    /** Identifier of the main namespace. */
    const MAIN_NAMESPACE = '__main__';

    protected $paths = [];
    protected $cache = [];
    protected $errorCache = [];

    private $rootPath;

    /**
     * @param string|array $paths    A path or an array of paths where to look for templates
     * @param string|null  $rootPath The root path common to all relative paths (null for getcwd())
     */
    public function __construct($paths = [], string $rootPath = '') {
        $this->rootPath = (null === $rootPath ? getcwd() : $rootPath) . \DIRECTORY_SEPARATOR;

        $realPath = realpath($rootPath);

        if (false !== $realPath) {
            $this->rootPath = $realPath . \DIRECTORY_SEPARATOR;
        }

        if ($paths) {
            $this->setPaths($paths);
        }
    }

    /**
     * Returns the paths to the templates.
     */
    public function getPaths(string $namespace = self::MAIN_NAMESPACE): array {
        return isset($this->paths[$namespace]) ? $this->paths[$namespace] : [];
    }

    /**
     * Returns the path namespaces.
     *
     * The main namespace is always defined.
     */
    public function getNamespaces(): array {
        return array_keys($this->paths);
    }

    /**
     * @param string|array $paths A path or an array of paths where to look for templates
     */
    public function setPaths($paths, string $namespace = self::MAIN_NAMESPACE): void {
        if (!\is_array($paths)) {
            $paths = [$paths];
        }

        $this->paths[$namespace] = [];
        foreach ($paths as $path) {
            $this->addPath($path, $namespace);
        }
    }

    /**
     * @throws LoaderError
     */
    public function addPath(string $path, string $namespace = self::MAIN_NAMESPACE): void {
        // invalidate the cache
        $this->cache = $this->errorCache = [];

        $checkPath = $this->isAbsolutePath($path) ? $path : $this->rootPath . $path;
        if (!is_dir($checkPath)) {
            throw new LoaderError(sprintf('The "%s" directory does not exist ("%s").', $path, $checkPath));
        }

        $this->paths[$namespace][] = rtrim($path, '/\\');
    }

    /**
     * @throws LoaderError
     */
    public function prependPath(string $path, string $namespace = self::MAIN_NAMESPACE): void {
        // invalidate the cache
        $this->cache = $this->errorCache = [];

        $checkPath = $this->isAbsolutePath($path) ? $path : $this->rootPath . $path;
        if (!is_dir($checkPath)) {
            throw new LoaderError(sprintf('The "%s" directory does not exist ("%s").', $path, $checkPath));
        }

        $path = rtrim($path, '/\\');

        if (!isset($this->paths[$namespace])) {
            $this->paths[$namespace][] = $path;
        } else {
            array_unshift($this->paths[$namespace], $path);
        }
    }

    public function getSourceContext(string $name): Source {
        if (null === $path = $this->findTemplate($name)) {
            return new Source('', $name, '');
        }

        return new Source(file_get_contents($path), $name, $path);
    }

    public function getCacheKey(string $name): string {
        if (null === $path = $this->findTemplate($name)) {
            return '';
        }
        $len = \strlen($this->rootPath);
        if (0 === strncmp($this->rootPath, $path, $len)) {
            return substr($path, $len);
        }

        return $path;
    }

    /**
     * @return bool
     */
    public function exists(string $name) {
        $name = $this->normalizeName($name);

        if (isset($this->cache[$name])) {
            return true;
        }

        return null !== $this->findTemplate($name, false);
    }

    public function isFresh(string $name, int $time): bool {
        // false support to be removed in 3.0
        if (null === $path = $this->findTemplate($name)) {
            return false;
        }

        return filemtime($path) < $time;
    }

    /**
     * @return string|null
     */
    protected function findTemplate(string $name, bool $throw = true) {
        $name = $this->normalizeName($name);

        if (isset($this->cache[$name])) {
            return $this->cache[$name];
        }

        if (isset($this->errorCache[$name])) {
            if (!$throw) {
                return null;
            }

            throw new LoaderError($this->errorCache[$name]);
        }

        try {
            $this->validateName($name);

            list($namespace, $shortname) = $this->parseName($name);
        } catch (LoaderError $e) {
            if (!$throw) {
                return null;
            }

            throw $e;
        }

        if (!isset($this->paths[$namespace])) {
            $this->errorCache[$name] = sprintf('There are no registered paths for namespace "%s".', $namespace);

            if (!$throw) {
                return null;
            }

            throw new LoaderError($this->errorCache[$name]);
        }

        foreach ($this->paths[$namespace] as $path) {
            if (!$this->isAbsolutePath($path)) {
                $path = $this->rootPath . $path;
            }

            if (is_file($path . '/' . $shortname)) {
                if (false !== $realpath = realpath($path . '/' . $shortname)) {
                    return $this->cache[$name] = $realpath;
                }

                return $this->cache[$name] = $path . '/' . $shortname;
            }
        }

        $this->errorCache[$name] = sprintf('Unable to find template "%s" (looked into: %s).', $name, implode(', ', $this->paths[$namespace]));

        if (!$throw) {
            return null;
        }

        throw new LoaderError($this->errorCache[$name]);
    }

    private function parseName(string $name, string $default = self::MAIN_NAMESPACE): array {
        if (isset($name[0]) && '@' == $name[0]) {
            if (false === $pos = strpos($name, '/')) {
                throw new LoaderError(sprintf('Malformed namespaced template name "%s" (expecting "@namespace/template_name").', $name));
            }

            $namespace = substr($name, 1, $pos - 1);
            $shortname = substr($name, $pos + 1);

            return [$namespace, $shortname];
        }

        return [$default, $name];
    }

    private function validateName(string $name): void {
        if (false !== strpos($name, "\0")) {
            throw new LoaderError('A template name cannot contain NUL bytes.');
        }

        $name = ltrim($name, '/');
        $parts = explode('/', $name);
        $level = 0;
        foreach ($parts as $part) {
            if ('..' === $part) {
                --$level;
            } elseif ('.' !== $part) {
                ++$level;
            }

            if ($level < 0) {
                throw new LoaderError(sprintf('Looks like you try to load a template outside configured directories (%s).', $name));
            }
        }
    }

    private function isAbsolutePath(string $file): bool {
        return strspn($file, '/\\', 0, 1)
            || (\strlen($file) > 3 && ctype_alpha($file[0])
                && ':' === $file[1]
                && strspn($file, '/\\', 2, 1))
            || null !== parse_url($file, PHP_URL_SCHEME);
    }
}
