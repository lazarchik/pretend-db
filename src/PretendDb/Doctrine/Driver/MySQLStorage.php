<?php
/**
 * @author: Eugene Lazarchik
 * @date: 7/13/16
 */

namespace PretendDb\Doctrine\Driver;


use PhpMyAdmin\SqlParser\Statements\CreateStatement;
use PhpMyAdmin\SqlParser\Statements\DropStatement;
use PhpMyAdmin\SqlParser\Statements\InsertStatement;
use PhpMyAdmin\SqlParser\Statements\SelectStatement;
use PhpMyAdmin\SqlParser\Statements\SetStatement;
use PretendDb\Doctrine\Driver\Expression\EvaluationContext;
use PretendDb\Doctrine\Driver\Parser\Expression\ExpressionInterface;
use PretendDb\Doctrine\Driver\Parser\Parser;

class MySQLStorage
{
    /** @var string */
    protected $currentDatabaseName;
    
    /** @var MySQLDatabase[] */
    protected $databases = [];
    
    /** @var Parser */
    protected $parser;

    /**
     * @param Parser $parser
     */
    public function __construct($parser)
    {
        $this->parser = $parser;
    }

    /**
     * @param string $currentDatabaseName
     * @throws \InvalidArgumentException
     */
    public function setCurrentDatabaseName($currentDatabaseName)
    {
        if (!$this->databaseExists($currentDatabaseName)) {
            throw new \InvalidArgumentException("Can't USE a database that doesn't exist: ".$currentDatabaseName
                .". Existing databases: ".join(", ", array_keys($this->databases)));
        }
        
        $this->currentDatabaseName = $currentDatabaseName;
    }

    /**
     * @return string
     */
    public function getCurrentDatabaseName()
    {
        return $this->currentDatabaseName;
    }

    /**
     * @param string|null $databaseName
     * @return MySQLDatabase
     * @throws \RuntimeException
     */
    public function getDatabase($databaseName)
    {
        if (null === $databaseName) {
            $databaseName = $this->currentDatabaseName;
        }
        
        if (!$this->databaseExists($databaseName)) {
            throw new \RuntimeException("Database doesn't exist: ".$databaseName);
        }
        
        return $this->databases[$databaseName];
    }

    /**
     * @param string $databaseName
     */
    public function createDatabase($databaseName)
    {
        $this->databases[$databaseName] = new MySQLDatabase();
    }

    /**
     * @param string $databaseName
     * @return bool
     */
    public function databaseExists($databaseName)
    {
        return array_key_exists($databaseName, $this->databases);
    }

    /**
     * @param string $queryString
     * @param array $boundParams
     * @return MySQLQueryResult
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function executeQuery($queryString, $boundParams)
    {
        // For some reason PHPMyAdmin's parser doesn't support USE statements.
        // Do a rough parsing with regular expressions for now.
        if (preg_match("~^[ \t]*USE `?([a-z\$_][a-z0-9\$_]+)[ \t]*`?$~i", $queryString, $useStatementMatch)) {
            
            return $this->executeUse($useStatementMatch[1], $boundParams);
        }
        
        $parser = new \PhpMyAdmin\SqlParser\Parser($queryString);
        
        $parsedStatement = $parser->statements[0];
        
        if ($parsedStatement instanceof SelectStatement) {
            return $this->executeSelect($parsedStatement, $boundParams);
        }
        
        if ($parsedStatement instanceof InsertStatement) {
            return $this->executeInsert($parsedStatement, $boundParams);
        }
        
        if ($parsedStatement instanceof SetStatement) {
            return new MySQLQueryResult(); // ignore for now
        }
        
        if ($parsedStatement instanceof DropStatement) {
            return $this->executeDrop($parsedStatement, $boundParams);
        }
        
        if ($parsedStatement instanceof CreateStatement) {
            return $this->executeCreate($parsedStatement, $boundParams);
        }
        
        throw new \RuntimeException("Only SELECT and INSERT statements are currently supported. Got: ".$queryString
            .". Parsed statement: ".var_export($parsedStatement, true));
    }

    /**
     * @param SelectStatement $selectStatement
     * @param array $boundParams
     * @return MySQLQueryResult
     * @throws \RuntimeException
     */
    protected function executeSelect($selectStatement, $boundParams)
    {
        $fromStatement = $selectStatement->from[0];
        
        $databaseName = $fromStatement->database;
        $tableName = $fromStatement->table;
        $tableAlias = $fromStatement->alias;
        $tableObject = $this->getDatabase($databaseName)->getTable($tableName);
        
        $tableNameOrAlias = $tableAlias ?: $tableName;
        
        $tableAliases = [$tableNameOrAlias => $tableName];
        
        if ($selectStatement->join) {
            
            $joinedTablesRows = [];
            foreach ($tableObject->getAllRows() as $tableRowFields) {
                $joinedTablesRows[] = [$tableNameOrAlias => $tableRowFields];
            }
            
            foreach ($selectStatement->join as $joinInfoObject) {
                $joinType = $joinInfoObject->type;
                $joinedTableName = $joinInfoObject->expr->table;
                $joinedDatabaseName = $joinInfoObject->expr->database ?: $databaseName;
                $joinedTableAlias = $joinInfoObject->expr->alias;
                $joinedTableNameOrAlias = $joinedTableAlias ?: $joinedTableName;
                $joinedTableObject = $this->getDatabase($joinedDatabaseName)->getTable($joinedTableName);
                $joinedTableFieldNames = $joinedTableObject->getColumnNames();
                
                $joinOnExpressionAST = $this->parser->parse($joinInfoObject->on[0]->expr);
                
                $newJoinedTablesRows = [];
                
                foreach ($joinedTablesRows as $joinedTablesRow) {
                    
                    $evaluationContext = new EvaluationContext();
                    $evaluationContext->addTableAliases($tableAliases);
                    $evaluationContext->setBoundParamValues($boundParams);
                    
                    foreach ($joinedTablesRow as $foundTableNameOrAlias => $joinedTablesRowFields) {
                        $evaluationContext->setTableRow($databaseName, $foundTableNameOrAlias, $joinedTablesRowFields);
                    }
                    
                    $tableAliases[$joinedTableNameOrAlias] = $joinedTableName;
                    
                    $joinedTableRows = $joinedTableObject->findRowsSatisfyingAnExpression(
                        $joinOnExpressionAST, $evaluationContext, $joinedDatabaseName, $joinedTableNameOrAlias
                    );
                    
                    if ($joinedTableRows) {
                        foreach ($joinedTableRows as $joinedTableRowFields) {
                            
                            $newJoinedTablesRows[] = array_merge(
                                $joinedTablesRow, [$joinedTableNameOrAlias => $joinedTableRowFields]);
                        }
                    } elseif ("LEFT" == $joinType) {
                        
                        $joinedTableRowFields = array_combine($joinedTableFieldNames,
                            array_fill(0, count($joinedTableFieldNames), null));
                        
                        $newJoinedTablesRows[] = array_merge(
                                $joinedTablesRow, [$joinedTableNameOrAlias => $joinedTableRowFields]);
                    }
                }
                
                $joinedTablesRows = $newJoinedTablesRows;
            }
        }
        
        $whereExpressionStrings = [];
        
        if (is_array($selectStatement->where)) {
            foreach ($selectStatement->where as $whereExpression) {
                $whereExpressionStrings[] = $whereExpression->expr;
            }
        }
        
        $fullWhereConditionString = join(" ", $whereExpressionStrings);
        
        if (empty($fullWhereConditionString)) {
            // In case where clause is missing, just use "1" as the condition. It always evaluates to true.
            $fullWhereConditionString = "1";
        }
        
        $parsedWhereConditionAST = $this->parser->parse($fullWhereConditionString);
        
        // Now just go over all rows in the table and evaluate where condition against each row
        
        $evaluationContext = new EvaluationContext();
        $evaluationContext->setBoundParamValues($boundParams);
        $evaluationContext->addTableAliases($tableAliases);
        
        if ($selectStatement->join) {
            $foundRows = [];
            foreach ($joinedTablesRows as $joinedTablesRow) {
                    
                $newEvaluationContext = clone $evaluationContext;
                
                foreach ($joinedTablesRow as $joinedTableNameOrAlias => $joinedTableRowFields) {

                    $newEvaluationContext->setTableRow($databaseName, $joinedTableNameOrAlias, $joinedTableRowFields);
                }
    
                $evaluationResult = $parsedWhereConditionAST->evaluate($newEvaluationContext);
    
                if ($evaluationResult) {
                    $foundRows[] = $joinedTablesRow;
                }
            }
        } else {
            
            $plainFoundRows = $tableObject->findRowsSatisfyingAnExpression($parsedWhereConditionAST,
                $evaluationContext, $databaseName, $tableNameOrAlias);
            
            $foundRows = [];
            foreach ($plainFoundRows as $tableRowFields) {
                $foundRows[] = [$tableNameOrAlias => $tableRowFields];
            }
            
            $foundRows = [
                $tableNameOrAlias => $tableObject->findRowsSatisfyingAnExpression(
                    $parsedWhereConditionAST, $evaluationContext, $databaseName, $tableNameOrAlias
                ),
            ];
        }
        
        $parsedSelectExpressions = [];
        foreach ($selectStatement->expr as $index => $selectExpressionInfo) {
            
            if ("*" == trim($selectExpressionInfo->expr)) {
                foreach ($tableObject->getColumnNames() as $fieldName) {
                    $parsedSelectExpressions[] = [
                        "alias" => $fieldName,
                        "AST" => $this->parser->parse($databaseName.".".$tableNameOrAlias.".".$fieldName),
                    ];
                }
                
                continue;
            }
            
            $parsedSelectExpressions[] = [
                "alias" => $selectExpressionInfo->alias,
                "AST" => $this->parser->parse($selectExpressionInfo->expr),
            ];
        }
        
        
        $queryResultsTableColumnMetas = [];
        foreach ($parsedSelectExpressions as $parsedSelectExpression) {
            $queryResultsTableColumnMetas[] = new MySQLColumnMeta($parsedSelectExpression["alias"]);
        }
        
        $queryResultsTable = new MySQLTable($queryResultsTableColumnMetas);
        
        $queryResults = [];
        foreach ($foundRows as $rowNumber => $foundRowTables) {
            
            $evaluationContext = new EvaluationContext();
            $evaluationContext->setBoundParamValues($boundParams);
            $evaluationContext->addTableAliases($tableAliases);
            
            foreach ($foundRowTables as $joinTableAliasOrName => $joinTableRowFields) {
                
                $evaluationContext->setTableRow($databaseName, $joinTableAliasOrName, $joinTableRowFields);
            }
            
            foreach ($parsedSelectExpressions as $parsedSelectExpression) {
                $selectExpressionAlias = $parsedSelectExpression["alias"];
                
                /** @var ExpressionInterface $selectExpressionAST */
                $selectExpressionAST = $parsedSelectExpression["AST"];
                
                $queryResults[$rowNumber][$selectExpressionAlias] = $selectExpressionAST->evaluate($evaluationContext);
            }
            
            $queryResultsTable->insertRow($queryResults[$rowNumber]);
        }
        
        $queryResultObject = new MySQLQueryResult();
        $queryResultObject->setQueryResultsTable($queryResultsTable);
        
        return $queryResultObject;
    }

    /**
     * @param string $value
     * @param array $boundParams
     * @return mixed
     */
    protected function evaluateValue($value, $boundParams)
    {
        if ("?" == $value) {
            return array_shift($boundParams);
        }
        
        return $value;
    }

    /**
     * @param string $databaseName
     * @param string $tableName
     * @param string[] $columns
     * @param array $valueFields
     * @param array $boundParams
     * @return int|null Last insert ID if ID's were autogenerated
     * @throws \RuntimeException
     */
    protected function insertRow($databaseName, $tableName, $columns, $valueFields, $boundParams)
    {
        $reindexedValues = [];
        
        foreach ($columns as $columnIndex => $columnName) {
            $value = $valueFields[$columnIndex];
            
            $reindexedValues[$columnName] = $this->evaluateValue($value, $boundParams);
        }
        
        $databaseObject = $this->getDatabase($databaseName);
        
        $storageTable = $databaseObject->getTable($tableName);
        
        try {
            $storageTable->insertRow($reindexedValues);
        } catch (\RuntimeException $e) {
            throw new \RuntimeException("Can't insert a row into table: "
                .$this->formatTableName($databaseName, $tableName).". ".$e->getMessage(), 0, $e);
        }
        
        /** @TODO support proper insert IDs */
        return $valueFields[0];
    }

    /**
     * @param string|null $databaseName
     * @param string $tableName
     * @return string
     */
    protected function formatTableName($databaseName, $tableName)
    {
        return ($databaseName ? $databaseName."." : "").$tableName;
    }

    /**
     * @param InsertStatement $insertStatement
     * @param array $boundParams
     * @return MySQLQueryResult
     * @throws \RuntimeException
     */
    protected function executeInsert($insertStatement, $boundParams)
    {
        $intoStatement = $insertStatement->into;
        
        $databaseName = $intoStatement->dest->database;
        $tableName = $intoStatement->dest->table;
        $columns = $intoStatement->columns;
        
        $values = $insertStatement->values;
        
        $queryResultObject = new MySQLQueryResult();
        $queryResultObject->setAffectedRowsCount(count($values));
        
        foreach ($values as $valueFields) {
            $insertID = $this->insertRow($databaseName, $tableName, $columns, $valueFields->values, $boundParams);
            
            $queryResultObject->setLastInsertID($insertID);
        }
        
        return $queryResultObject;
    }

    /**
     * @param DropStatement $parsedStatement
     * @param array $boundParams
     * @return MySQLQueryResult
     * @throws \InvalidArgumentException
     */
    protected function executeDrop($parsedStatement, $boundParams)
    {
        $databaseOptionPresent = in_array("DATABASE", $parsedStatement->options->options);
        $ifExistsOptionPresent = in_array("IF EXISTS", $parsedStatement->options->options);
        
        if (!$databaseOptionPresent) {
            throw new \InvalidArgumentException(
                "DROP DATABASE is the only DROP statement that is currently supported. Got: "
                    .var_export($parsedStatement, true));
        }
        
        foreach ($parsedStatement->fields as $infoAboutDatabaseToDrop) {
            if (!$ifExistsOptionPresent && !$this->databaseExists($infoAboutDatabaseToDrop->table)) {
                throw new \InvalidArgumentException("Can't drop database that doesn't exist: ".$infoAboutDatabaseToDrop
                    .". Existing databases: ".join(", ", array_keys($this->databases)));
            }
            
            unset($this->databases[$infoAboutDatabaseToDrop->table]);
        }
        
        return new MySQLQueryResult();
    }

    /**
     * @param CreateStatement $parsedStatement
     * @param array $boundParams
     * @return MySQLQueryResult
     * @throws \InvalidArgumentException
     */
    protected function executeCreate($parsedStatement, $boundParams)
    {
        $databaseOptionPresent = in_array("DATABASE", $parsedStatement->options->options);
        
        if (!$databaseOptionPresent) {
            return $this->executeCreateTable($parsedStatement, $boundParams);
        }
        
        
        return $this->executeCreateDatabase($parsedStatement, $boundParams);
    }

    /**
     * @param string $databaseName
     * @param array $boundParams
     * @return MySQLQueryResult
     * @throws \InvalidArgumentException
     */
    protected function executeUse($databaseName, $boundParams)
    {
        $this->setCurrentDatabaseName($databaseName);
        
        return new MySQLQueryResult();
    }


    /**
     * @param CreateStatement $parsedStatement
     * @param array $boundParams
     * @return MySQLQueryResult
     * @throws \InvalidArgumentException
     */
    protected function executeCreateDatabase($parsedStatement, $boundParams)
    {
        $ifNotExistsOptionPresent = in_array("IF NOT EXISTS", $parsedStatement->options->options);
        
        $databaseName = $parsedStatement->name->table;
        
        if (empty($databaseName)) {
            throw new \InvalidArgumentException(
                "Database name in CREATE DATABASE must not be empty. Got: " . $databaseName);
        }
        
        if (!$ifNotExistsOptionPresent && $this->databaseExists($databaseName)) {
            throw new \InvalidArgumentException("Can't create database that already exists: ".$databaseName);
        }

        $this->createDatabase($databaseName);
        
        return new MySQLQueryResult();
    }

    /**
     * @param CreateStatement $parsedStatement
     * @param array $boundParams
     * @return MySQLQueryResult
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    protected function executeCreateTable($parsedStatement, $boundParams)
    {
        $ifNotExistsOptionPresent = in_array("IF NOT EXISTS", $parsedStatement->options->options);
        
        $databaseName = $parsedStatement->name->database;
        $tableName = $parsedStatement->name->table;
        
        /** @TODO implement handling these options */
        $entityOptions = $parsedStatement->entityOptions;
        
        if (empty($tableName)) {
            throw new \InvalidArgumentException("Table name in CREATE TABLE must not be empty. Got: ".$tableName);
        }
        
        $databaseObject = $this->getDatabase($databaseName);
        
        if (!$ifNotExistsOptionPresent && !$databaseObject->tableExists($tableName)) {
            throw new \InvalidArgumentException("Can't create table that already exists: ".$tableName);
        }
        
        $columnsMeta = [];
        foreach ($parsedStatement->fields as $parsedFieldToCreate) {
            
            $columnsMeta[] = new MySQLColumnMeta($parsedFieldToCreate->name);
            
            /** @TODO implement data types and other column options */
        }
        
        $databaseObject->createTable($tableName, $columnsMeta);
        
        return new MySQLQueryResult();
    }
}
