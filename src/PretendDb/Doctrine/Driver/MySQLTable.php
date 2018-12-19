<?php

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
    
    /** @var array [$partitionName => $partitionValue] */
    protected $partitions = [];
    
    /** @var int */
    protected $autoIncrementValue = 0;
    
    /** @var int|null */
    protected $autoIncrementColumnIndex;

    /**
     * @param MySQLColumnMeta[] $columns
     */
    public function __construct(array $columns)
    {
        $this->columns = $columns;
        
        $this->columnIndexes = [];
        foreach ($columns as $columnIndex => $columnMetaObject) {
            $this->columnIndexes[$columnMetaObject->getName()] = $columnIndex;
            
            if ($columnMetaObject->isAutoincremented()) {
                $this->autoIncrementColumnIndex = $columnIndex;
            }
        }
    }

    public function getColumnIndex(string $columnName): int
    {
        if (!array_key_exists($columnName, $this->columnIndexes)) {
            throw new \RuntimeException("Can't determine column index for column ".$columnName." in table ");
        }
        
        return $this->columnIndexes[$columnName];
    }

    /**
     * @param array $rowFields
     * @return int|null Generated autoincrement ID if applicable
     */
    public function insertRow(array $rowFields): ?int
    {
        $generatedAutoIncrementID = null;
        $row = [];
        foreach ($this->columns as $columnIndex => $columnMeta) {
            $columnName = $columnMeta->getName();
            
            if ($columnMeta->isAutoincremented()) {
                if (!empty($rowFields[$columnName])) {
                    $row[$columnIndex] = $rowFields[$columnName];
                    $this->autoIncrementValue = (int)$rowFields[$columnName];
                } else {
                    $generatedAutoIncrementID = ++$this->autoIncrementValue;
                    $row[$columnIndex] = $generatedAutoIncrementID;
                }
                continue;
            }
            
            if (array_key_exists($columnName, $rowFields) && null !== $rowFields[$columnName]) {
                $row[$columnIndex] = $rowFields[$columnName];
                continue;
            }
            
            if ($defaultValue = $columnMeta->getDefaultValue()) {
                if ($defaultValue->isCurrentTimestamp()) {
                    $row[$columnIndex] = time();
                    continue;
                }
                
                $row[$columnIndex] = $defaultValue->getValue();
                continue;
            }
            
            if ($columnMeta->isNullable()) {
                $row[$columnIndex] = null;
                continue;
            }
            
            // TEXT fields cannot have a default value and omitting them sets them to an empty string, I think
            
            $row[$columnIndex] = "";
            //throw new \RuntimeException("Missing value for required field ".$columnName);
        }
        
        $this->rows[] = $row;
        
        return $generatedAutoIncrementID;
    }

    public function findRowsSatisfyingAnExpression(
        ExpressionInterface $expressionAST,
        EvaluationContext $evaluationContext,
        ?string $databaseName,
        string $tableNameOrAlias
    ): array {
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

    protected function populateRowValuesWithFieldNames(array $rowValues): array
    {
        $rowFields = [];
        foreach ($rowValues as $columnIndex => $fieldValue) {
            
            $columnName = $this->columns[$columnIndex]->getName();
            
            $rowFields[$columnName] = $fieldValue;
        }
        
        return $rowFields;
    }

    public function getAllRows(): array
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
    public function getColumnNames(): array
    {
        return array_flip($this->columnIndexes);
    }

    public function truncate(): void
    {
        $this->rows = [];
    }

    /**
     * @TODO More graceful checks (if partition already exists, etc)
     * @param string $partitionName
     * @param string $partitionValue
     */
    public function addPartition(string $partitionName, string $partitionValue): void
    {
        $this->partitions[$partitionName] = $partitionValue;
    }
}
