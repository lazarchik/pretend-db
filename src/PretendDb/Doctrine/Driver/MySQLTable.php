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
    /** @var MySQLColumnMeta[] */
    protected $columns;
    
    /** @var int[] */
    protected $columnIndexes;
    
    /** @var array */
    protected $rows = [];

    /**
     * @param MySQLColumnMeta[] $columns
     * @internal param string $tableName
     */
    public function __construct($columns)
    {
        $this->columns = $columns;
        
        $this->columnIndexes = [];
        foreach ($columns as $columnIndex => $columnMetaObject) {
            $this->columnIndexes[$columnMetaObject->getName()] = $columnIndex;
        }
    }

    /**
     * @param array $rows
     * @throws \RuntimeException
     */
    public function setRows($rows)
    {
        if (array_keys($rows) != $this->columnIndexes) {
            throw new \RuntimeException("Can't set table rows since the number of columns is incorrect. Expected "
                .count($this->columnIndexes)." columns. Got: ".var_export($rows, true));
        }
        
        $this->rows = $rows;
    }

    /**
     * @param string $columnName
     * @throws \RuntimeException
     * @return int;
     */
    public function getColumnIndex($columnName)
    {
        if (!array_key_exists($columnName, $this->columnIndexes)) {
            throw new \RuntimeException("Can't determine column index for column ".$columnName." in table ");
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
     * @param ExpressionInterface $expressionAST
     * @param EvaluationContext $evaluationContext
     * @param $databaseName
     * @param param string $tableNameOrAlias
     * @return array
     */
    public function findRowsSatisfyingAnExpression(
        $expressionAST,
        $evaluationContext,
        $databaseName,
        $tableNameOrAlias
    )
    {
        $foundRows = [];
        
        foreach ($this->rows as $rowValues) {

            $rowValuesWithFieldNames = $this->populateRowValuesWithFieldNames($rowValues);

            $newEvaluationContext = clone $evaluationContext;
            $newEvaluationContext->setTableRow($databaseName, $tableNameOrAlias, $rowValuesWithFieldNames);

            $evaluationResult = $expressionAST->evaluate($newEvaluationContext);
            
            if ($evaluationResult) {
                $foundRows[] = $rowValuesWithFieldNames;
            }
        }

        return $foundRows;
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
     * @return array
     */
    public function getAllRows()
    {
        $foundRows = [];
        foreach ($this->rows as $rowValues) {
            $foundRows[] = $this->populateRowValuesWithFieldNames($rowValues);
        }
        
        return $foundRows;
    }

    /**
     * @return string[]
     */
    public function getColumnNames()
    {
        return array_flip($this->columnIndexes);
    }
}
