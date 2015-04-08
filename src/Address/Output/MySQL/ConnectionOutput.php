<?php
namespace Address\Output\MySQL;

use Address\Output\Output;

class ConnectionOutput extends Output
{
    private $conn;
    private $preparedStatements = array();

    public function __construct($dsn, $dbUser, $dbPassword, $options)
    {
        $this->conn = new \PDO($dsn, $dbUser, $dbPassword, $options);

        // Шаблон конвертации Схемы
        $template = new \DOMDocument('1.0', 'UTF-8');
        $template->load(self::$resourcesPath . '/mysql-schema.xsl');
        $this->xslt = new \XSLTProcessor();
        $this->xslt->importStylesheet($template);
    }

    public function handleSchemaFile($tableName, \DOMDocument $schemaDocument)
    {
        $this->xslt->setParameter('', 'tableName', $tableName);
        $sqlDefinition = $this->xslt->transformToXml($schemaDocument);

        $this->conn->exec($sqlDefinition);
    }

    public function handleData($tableName, array $fields)
    {
        // Подготавливаем запрос
        if (isset($this->preparedStatements[$tableName]) === false) {

            // В XML'ке пропускаются необязательные столбцы, забираем полный вариант TODO Не безопасно базы
            $query = $this->conn->query("DESCRIBE " . $tableName);
            $columns = $query->fetchAll(\PDO::FETCH_COLUMN);

            $query = "INSERT INTO `%s` (`%s`) VALUES (:%s)";
            $query = sprintf(
                $query,
                $tableName,
                implode("`, `", $columns),
                implode(", :", $columns)
            );

            $this->preparedStatements[$tableName] = $this->conn->prepare($query);
        }

        $this->preparedStatements[$tableName]->execute($fields);
    }
}