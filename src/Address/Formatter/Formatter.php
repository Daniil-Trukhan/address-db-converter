<?php
namespace Address\Formatter;

abstract class Formatter implements FormatterInterface
{
    abstract function handleSchemaFile($tableName, \DOMDocument $schemaDocument);
    abstract function handleDataFile($tableName, array $fields);
}