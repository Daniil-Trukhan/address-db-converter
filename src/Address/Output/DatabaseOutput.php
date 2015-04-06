<?php
namespace Address\Output;

class DatabaseOutput extends Output
{
    private $conn;

    public function __construct($dsn, $databaseUser, $databasePassword)
    {
        $this->conn = new \PDO($dsn, $databaseUser, $databasePassword, array());
    }

    public function receiveSchemaResult($conversionResult)
    {
        $this->conn->exec($conversionResult);
    }

    public function receiveDataResult($conversionResult)
    {
        $this->conn->exec($conversionResult);
    }
}