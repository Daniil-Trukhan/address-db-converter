<?php
namespace Address\Output;

interface OutputInterface
{
	/**
	 * Обработка файла со схемой.
	 * 
	 */
    function handleSchemaFile($tableName, \DOMDocument $schemaDocument);
	
	/**
	 * Обработка строки данных.
	 * 
	 */
    function handleDataRow($tableName, array $fields);
} 