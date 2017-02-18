<?php
/**
 * @author: Eugene Lazarchik
 * @date: 2/18/17
 */

namespace PretendDb\Doctrine\Driver;


class MySQLTable
{
    /** @var string */
    protected $tableName;
    
    /** @var MySQLColumnMeta[] */
    protected $columns;
    
    /** @var int[] */
    protected $columnIndexes;
    
    /** @var array */
    protected $rows = [];

    /**
     * @param string $tableName
     * @param MySQLColumnMeta[] $columns
     */
    public function __construct($tableName, $columns)
    {
        $this->tableName = $tableName;
        $this->columns = $columns;
        
        foreach ($columns as $columnIndex => $columnMetaObject) {
            $this->columnIndexes[$columnMetaObject->getName()] = $columnIndex;
        }
    }

    /**
     * @param string $columnName
     * @throws \RuntimeException
     * @return int;
     */
    public function getColumnIndex($columnName)
    {
        if (!array_key_exists($columnName, $this->columnIndexes)) {
            throw new \RuntimeException("Can't determine column index for column ".$columnName
                ." in table ".$this->tableName);
        }
        
        return $this->columnIndexes[$columnName];
    }

    /**
     * @return array
     */
    public function getEmptyRow()
    {
        $row = [];
        
        foreach ($this->columnIndexes as $columnName => $columnIndex) {
            $row[$columnIndex] = null;
        }
        
        return $row;
    }

    /**
     * @param array $rowFields
     */
    public function insertRow($rowFields)
    {
        var_dump("\$rowFields", $rowFields);
        $row = $this->getEmptyRow();
        
        foreach ($rowFields as $columnName => $fieldValue) {
            
            $columnIndex = $this->getColumnIndex($columnName);
            
            $row[$columnIndex] = $fieldValue;
        }
        
        $this->rows[] = $row;
    }
}
