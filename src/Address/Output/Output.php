<?php
namespace Address\Output;

abstract class Output implements OutputInterface
{
    public static $resourcesPath = '';
    public static $outputPath = '';
    abstract function handleSchemaFile($tableName, \DOMDocument $schemaDocument);
    abstract function handleData($tableName, array $fields);
}