<?php
namespace Address\Output;

interface OutputInterface
{
    function handleSchemaFile($tableName, \DOMDocument $schemaDocument);
    function handleData($tableName, array $fields);
} 