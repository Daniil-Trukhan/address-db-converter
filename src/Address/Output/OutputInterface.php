<?php
namespace Address\Output;

interface OutputInterface
{
    function handleSchemaFile($tableName, \DOMDocument $schemaDocument);
    function handleDataRow($tableName, array $fields);
    function handleIndex();
} 