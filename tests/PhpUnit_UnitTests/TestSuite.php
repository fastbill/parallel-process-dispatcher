<?php

require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * Static test suite.
 */
class PhpQaGraphs_TestSuite extends PHPUnit_Framework_TestSuite
{
    /**
     * Constructor
     */
    public function __construct()
    {
        // Load all tests in the directory
        $path = realpath(dirname(__FILE__));
        $directory = new RecursiveDirectoryIterator($path);
        $dirIter = new RecursiveIteratorIterator($directory);

        $pattern = '#Test\.php$#';
        if (isset($_SERVER['INCLUDE_PATTERN']) && '' != trim($_SERVER['INCLUDE_PATTERN'])) {
            $pattern = '/.*' . $_SERVER['INCLUDE_PATTERN'] . '.*Test\.php$/i';
        }

        /** @var SplFileInfo $file */
        foreach ($dirIter as $file) {
            if (!$file->isFile() || strpos($file->getPathname(), '/.svn/')) {
                continue;
            }

            $name = $file->getPathname();
            if (!preg_match($pattern, $name)) {
                continue;
            }

            $this->addTestFile($file->getPathname());
        }
    }

    /**
     * Creates the suite.
     */
    public static function suite()
    {
        self::init();
        return new self();
    }

    static public function init()
    {
        // Set default tine zone
        date_default_timezone_set('Europe/Berlin');
    }

}
