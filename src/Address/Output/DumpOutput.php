<?php
namespace Address\Output;

class DumpOutput extends Output
{
    private $schemaFile;
    private $dumpFile;

    public function __construct()
    {
        $schemaPathname = self::$outputPath . '/mysql-schema.sql';
        $this->schemaFile = fopen($schemaPathname, 'c');

        $dumpPathname = self::$outputPath . '/mysql-dump.sql';
        $this->dumpFile = fopen($dumpPathname, 'c');
    }

    public function __destruct()
    {
        fclose($this->schemaFile);
        fclose($this->dumpFile);
    }

    public function receiveSchemaResult($conversionResult)
    {
        fwrite($this->schemaFile, $conversionResult);
    }

    function receiveDataResult($conversionResult)
    {
        fwrite($this->dumpFile, $conversionResult);
    }
}