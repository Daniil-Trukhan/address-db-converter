<?php
namespace Address\Output;

abstract class Output implements OutputInterface
{
	/**
	 * Путь к папке с ресурсами (шаблоны, ключи, etc)
	 * @var string
	 */
    public static $resourcesPath = '';
	
	/**
	 * Путь к папке с результатом конверсии
	 * @var string
	 */
    public static $outputPath = '';
	
	/**
	 * @inheritdoc
	 */
    abstract function handleSchemaFile($tableName, \DOMDocument $schemaDocument);
	
	/**
	 * @inheritdoc
	 */
    abstract function handleDataRow($tableName, array $fields);
}