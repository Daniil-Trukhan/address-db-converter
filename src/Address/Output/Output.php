<?php
namespace Address\Output;

abstract class Output implements OutputInterface
{
	/**
	 * ���� � ����� � ��������� (�������, �����, etc)
	 * @var string
	 */
    public static $resourcesPath = '';
	
	/**
	 * ���� � ����� � ����������� ���������
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