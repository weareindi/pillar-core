<?php

namespace PillarCore\Twig;

use Twig_LoaderInterface;
use Twig_ExistsLoaderInterface;
use Twig_SourceContextLoaderInterface;
use Twig_Error_Loader;
use Twig_Source;

/**
 * A custom loader for Twig
 *
 * We're assuming the filename for each template is 'template.twig'.
 * Using this loader we don't need to add 'template.twig' to our Twig includes
 */
class TwigCustomLoader implements Twig_LoaderInterface, Twig_ExistsLoaderInterface, Twig_SourceContextLoaderInterface
{
    /**
     * Modified normalizeName to include template filename extension
     * @param  String $name
     * @return String $name
     */
    private function normalizeName($name) {
        $name = preg_replace('#/{2,}#', '/', str_replace('\\', '/', $name));
        $name = $this->appendTemplateFilename($name);

        return $name;
    }

    /**
     * Append pattern name with our expected file extension
     * @param  String $name
     * @return String $name
     */
    private function appendTemplateFilename($name) {
        if ('template.twig' !== substr($name, -strlen('template.twig'))) {
            return $name . '/template.twig';
        }

        return $name;
    }

    /**
     *
     *
     * EVERYTHING BELOW THIS LINE IS UNMODIFIED
     * Original found in Twig package /twig/lib/Twig/Loader/Filesystem.php
     *
     *
     */

    /** Identifier of the main namespace. */
    const MAIN_NAMESPACE = '__main__';

    protected $paths = array();
    protected $cache = array();
    protected $errorCache = array();

    private $rootPath;

    /**
     * @param string|array $paths    A path or an array of paths where to look for templates
     * @param string|null  $rootPath The root path common to all relative paths (null for getcwd())
     */
    public function __construct($paths = array(), $rootPath = null)
    {
        $this->rootPath = (null === $rootPath ? getcwd() : $rootPath).DIRECTORY_SEPARATOR;
        if (false !== $realPath = realpath($rootPath)) {
            $this->rootPath = $realPath.DIRECTORY_SEPARATOR;
        }

        if ($paths) {
            $this->setPaths($paths);
        }
    }

    /**
     * Returns the paths to the templates.
     *
     * @param string $namespace A path namespace
     *
     * @return array The array of paths where to look for templates
     */
    public function getPaths($namespace = self::MAIN_NAMESPACE)
    {
        return isset($this->paths[$namespace]) ? $this->paths[$namespace] : array();
    }

    /**
     * Returns the path namespaces.
     *
     * The main namespace is always defined.
     *
     * @return array The array of defined namespaces
     */
    public function getNamespaces()
    {
        return array_keys($this->paths);
    }

    /**
     * Sets the paths where templates are stored.
     *
     * @param string|array $paths     A path or an array of paths where to look for templates
     * @param string       $namespace A path namespace
     */
    public function setPaths($paths, $namespace = self::MAIN_NAMESPACE)
    {
        if (!is_array($paths)) {
            $paths = array($paths);
        }

        $this->paths[$namespace] = array();
        foreach ($paths as $path) {
            $this->addPath($path, $namespace);
        }
    }

    /**
     * Adds a path where templates are stored.
     *
     * @param string $path      A path where to look for templates
     * @param string $namespace A path namespace
     *
     * @throws Twig_Error_Loader
     */
    public function addPath($path, $namespace = self::MAIN_NAMESPACE)
    {
        // invalidate the cache
        $this->cache = $this->errorCache = array();

        $checkPath = $this->isAbsolutePath($path) ? $path : $this->rootPath.$path;
        if (!is_dir($checkPath)) {
            throw new Twig_Error_Loader(sprintf('The "%s" directory does not exist ("%s").', $path, $checkPath));
        }

        $this->paths[$namespace][] = rtrim($path, '/\\');
    }

    /**
     * Prepends a path where templates are stored.
     *
     * @param string $path      A path where to look for templates
     * @param string $namespace A path namespace
     *
     * @throws Twig_Error_Loader
     */
    public function prependPath($path, $namespace = self::MAIN_NAMESPACE)
    {
        // invalidate the cache
        $this->cache = $this->errorCache = array();

        $checkPath = $this->isAbsolutePath($path) ? $path : $this->rootPath.$path;
        if (!is_dir($checkPath)) {
            throw new Twig_Error_Loader(sprintf('The "%s" directory does not exist ("%s").', $path, $checkPath));
        }

        $path = rtrim($path, '/\\');

        if (!isset($this->paths[$namespace])) {
            $this->paths[$namespace][] = $path;
        } else {
            array_unshift($this->paths[$namespace], $path);
        }
    }

    public function getSourceContext($name)
    {
        $path = $this->findTemplate($name);

        return new Twig_Source(file_get_contents($path), $name, $path);
    }

    public function getCacheKey($name)
    {
        $path = $this->findTemplate($name);
        $len = strlen($this->rootPath);
        if (0 === strncmp($this->rootPath, $path, $len)) {
            return substr($path, $len);
        }

        return $path;
    }

    public function exists($name)
    {
        $name = $this->normalizeName($name);

        if (isset($this->cache[$name])) {
            return true;
        }

        return false !== $this->findTemplate($name, false);
    }

    public function isFresh($name, $time)
    {
        return filemtime($this->findTemplate($name)) < $time;
    }

    /**
     * Checks if the template can be found.
     *
     * @param string $name  The template name
     * @param bool   $throw Whether to throw an exception when an error occurs
     *
     * @return string|false The template name or false
     */
    protected function findTemplate($name, $throw = true)
    {
        $name = $this->normalizeName($name);

        if (isset($this->cache[$name])) {
            return $this->cache[$name];
        }

        if (isset($this->errorCache[$name])) {
            if (!$throw) {
                return false;
            }

            throw new Twig_Error_Loader($this->errorCache[$name]);
        }

        $this->validateName($name);

        list($namespace, $shortname) = $this->parseName($name);

        if (!isset($this->paths[$namespace])) {
            $this->errorCache[$name] = sprintf('There are no registered paths for namespace "%s".', $namespace);

            if (!$throw) {
                return false;
            }

            throw new Twig_Error_Loader($this->errorCache[$name]);
        }

        foreach ($this->paths[$namespace] as $path) {
            if (!$this->isAbsolutePath($path)) {
                $path = $this->rootPath.'/'.$path;
            }

            if (is_file($path.'/'.$shortname)) {


                if (false !== $realpath = realpath($path.'/'.$shortname)) {
                    return $this->cache[$name] = $realpath;
                }

                return $this->cache[$name] = $path.'/'.$shortname;
            }
        }

        $this->errorCache[$name] = sprintf('Unable to find template "%s" (looked into: %s).', $name, implode(', ', $this->paths[$namespace]));

        if (!$throw) {
            return false;
        }

        throw new Twig_Error_Loader($this->errorCache[$name]);
    }

    private function parseName($name, $default = self::MAIN_NAMESPACE)
    {
        if (isset($name[0]) && '@' == $name[0]) {
            if (false === $pos = strpos($name, '/')) {
                throw new Twig_Error_Loader(sprintf('Malformed namespaced template name "%s" (expecting "@namespace/template_name").', $name));
            }

            $namespace = substr($name, 1, $pos - 1);
            $shortname = substr($name, $pos + 1);

            return array($namespace, $shortname);
        }

        return array($default, $name);
    }

    private function validateName($name)
    {
        if (false !== strpos($name, "\0")) {
            throw new Twig_Error_Loader('A template name cannot contain NUL bytes.');
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
                throw new Twig_Error_Loader(sprintf('Looks like you try to load a template outside configured directories (%s).', $name));
            }
        }
    }

    private function isAbsolutePath($file)
    {
        return strspn($file, '/\\', 0, 1)
            || (strlen($file) > 3 && ctype_alpha($file[0])
                && ':' === $file[1]
                && strspn($file, '/\\', 2, 1)
            )
            || null !== parse_url($file, PHP_URL_SCHEME)
        ;
    }
}
