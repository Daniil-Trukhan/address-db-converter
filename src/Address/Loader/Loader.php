<?php
namespace Address\Loader;

abstract class Loader implements LoaderInterface
{
    public static $sourcePath = '';
    abstract function getSchemaFiles($extension);
    abstract function getDataFiles($extension);
}