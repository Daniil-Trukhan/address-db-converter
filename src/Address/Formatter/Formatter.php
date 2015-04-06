<?php
namespace Address\Formatter;

abstract class Formatter implements FormatterInterface
{
    public static $resourcesPath = '';
    abstract function handleSchemaFile($tableName, \DOMDocument $schemaDocument);
    abstract function handleDataFile($tableName, array $fields);
}