<?php
namespace Address\Output;

abstract class Output implements OutputInterface
{
    public static $outputPath = '';
    abstract function receiveSchemaResult($conversionResult);
    abstract function receiveDataResult($conversionResult);
}