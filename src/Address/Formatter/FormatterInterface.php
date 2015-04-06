<?php
namespace Address\Formatter;

interface FormatterInterface
{
    function handleSchemaFile($tableName, \DOMDocument $schemaDocument);
    function handleDataFile($tableName, array $fields);
} 