<?php
namespace Address\Output\MySQL;

use Address\Output\Output;

class ConnectionOutput extends Output
{
	/**
	 * @var \PDO
	 */
    private $conn;
	
	/**
	 * @var array
	 */
    private $preparedStatements = array();
	
	/**
	 * @var array
	 */
	private $tableColumns = array();

	/**
	 * 
	 */
    public function __construct(\PDO $conn)
    {
		$this->conn = $conn;
		
        // Шаблон конвертации Схемы
        $template = new \DOMDocument('1.0', 'UTF-8');
        $template->load(self::$resourcesPath . '/mysql-schema.xsl');
        $this->xslt = new \XSLTProcessor();
        $this->xslt->importStylesheet($template);
    }

	/**
	 * @inheritdoc
	 */
    public function handleSchemaFile($tableName, \DOMDocument $schemaDocument)
    {
        $this->xslt->setParameter('', 'tableName', $tableName);
        $query = $this->xslt->transformToXml($schemaDocument);
        $this->conn->exec($query);
    }

	/**
	 * @inheritdoc
	 */
    public function handleDataRow($tableName, array $fields)
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
			$this->tableColumns[$tableName] = $columns;
        }

		$fields += $this->tableColumns[$tableName]; // Наибыстрейший способ
        $this->preparedStatements[$tableName]->execute($fields);
    }
	
	public function handleIndex()
    {
        
    }
}