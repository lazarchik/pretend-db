<?php
/**
 * @author: Eugene Lazarchik
 * @date: 2/18/17
 */

namespace PretendDb\Doctrine\Driver;


use PretendDb\Doctrine\Driver\Expression\EvaluationContext;
use PretendDb\Doctrine\Driver\Parser\Expression\ExpressionInterface;

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
     * @throws \RuntimeException
     */
    public function insertRow($rowFields)
    {
        $row = $this->getEmptyRow();
        
        foreach ($rowFields as $columnName => $fieldValue) {
            
            $columnIndex = $this->getColumnIndex($columnName);
            
            $row[$columnIndex] = $fieldValue;
        }
        
        $this->rows[] = $row;
    }

    /**
     * @param array $rowValues
     * @return array
     */
    protected function populateRowValuesWithFieldNames($rowValues)
    {
        $rowFields = [];
        foreach ($rowValues as $columnIndex => $fieldValue) {
            
            $columnName = $this->columns[$columnIndex]->getName();
            
            $rowFields[$columnName] = $fieldValue;
        }
        
        return $rowFields;
    }

    /**
     * @param ExpressionInterface $expressionAST
     * @param string $tableNameOrAlias
     * @param array $boundParamValues
     * @return array
     */
    public function findRowsSatisfyingAnExpression($expressionAST, $boundParamValues, $tableNameOrAlias)
    {
        // TODO: properly support multiple databases.
        $databaseName = "default_database";
        
        $foundRows = [];
        foreach ($this->rows as $rowValues) {
            
            $rowValuesWithFieldNames = $this->populateRowValuesWithFieldNames($rowValues);
            
            $evaluationContext = new EvaluationContext();
            $evaluationContext->setTableRow($databaseName, $tableNameOrAlias, $rowValuesWithFieldNames);
            //$evaluationContext->addTableAlias($tableNameOrAlias, $this->tableName);
            $evaluationContext->setBoundParamValues($boundParamValues);
        
            $evaluationResult = $expressionAST->evaluate($evaluationContext);
            
            if ($evaluationResult) {
                $foundRows[] = $rowValuesWithFieldNames;
            }
        }
        
        return $foundRows;
    }
}
