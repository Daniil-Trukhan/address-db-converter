<?php
namespace Address\Loader;

class FileLoader extends Loader
{
    public function getSchemaFiles($extension = 'xsd')
    {
        $files = array();

        /** @var $file \DirectoryIterator */
        foreach (new \DirectoryIterator(self::$sourcePath) as $file) {
            if ($file->isDot() === true || $file->getExtension() !== $extension) {
                continue;
            }

            $files[] = $file;
        }

        return $files;
    }

    public function getDataFiles($extension = 'XML')
    {
        $files = array();

        /** @var $file \DirectoryIterator */
        foreach (new \DirectoryIterator(self::$sourcePath) as $file) {
            if ($file->isDot() === true || $file->getExtension() !== $extension) {
                continue;
            }

            $files[] = $file;
        }

        return $files;
    }
}