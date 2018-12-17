<?php

namespace PretendDb\Doctrine\Driver;


use PhpMyAdmin\SqlParser\Statements\AlterStatement;
use PhpMyAdmin\SqlParser\Statements\CreateStatement;
use PhpMyAdmin\SqlParser\Statements\DropStatement;
use PhpMyAdmin\SqlParser\Statements\InsertStatement;
use PhpMyAdmin\SqlParser\Statements\SelectStatement;
use PhpMyAdmin\SqlParser\Statements\SetStatement;
use PhpMyAdmin\SqlParser\Statements\TruncateStatement;
use PhpMyAdmin\SqlParser\Token;
use PretendDb\Doctrine\Driver\Expression\EvaluationContext;
use PretendDb\Doctrine\Driver\Parser\Expression\ExpressionInterface;
use PretendDb\Doctrine\Driver\Parser\Expression\TableFieldExpression;
use PretendDb\Doctrine\Driver\Parser\Parser;

class MySQLServer
{
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
     * @return string[]
     */
    public function getExistingDatabaseNames()
    {
        return array_keys($this->databases);
    }

    /**
     * @param string|null $databaseName
     * @param MySQLConnection $connection
     * @return MySQLDatabase
     * @throws \RuntimeException
     */
    public function getDatabase($databaseName, MySQLConnection $connection)
    {
        if (null === $databaseName) {
            $databaseName = $connection->getCurrentDatabaseName();
        }
        
        if (!$this->databaseExists($databaseName)) {
            throw new \RuntimeException("Database doesn't exist: `".$databaseName
                ."`. Existing databases: ".var_export($this->databases, true));
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
     * @param MySQLConnection $connection
     * @return MySQLQueryResult
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function executeQuery($queryString, $boundParams, MySQLConnection $connection)
    {
        try {
            // For some reason PHPMyAdmin's parser doesn't support USE statements.
            // Do a rough parsing with regular expressions for now.
            if (preg_match("~^[ \t]*USE `?([a-z\$_][a-z0-9\$_]+)[ \t]*`?$~i", $queryString, $useStatementMatch)) {

                return $this->executeUse($useStatementMatch[1], $boundParams, $connection);
            }

            $parser = new \PhpMyAdmin\SqlParser\Parser($queryString);

            $parsedStatement = $parser->statements[0];

            if ($parsedStatement instanceof SelectStatement) {
                return $this->executeSelect($parsedStatement, $boundParams, $connection);
            }

            if ($parsedStatement instanceof InsertStatement) {
                return $this->executeInsert($parsedStatement, $boundParams, $connection);
            }

            if ($parsedStatement instanceof SetStatement) {
                return new MySQLQueryResult(); // ignore for now
            }

            if ($parsedStatement instanceof DropStatement) {
                return $this->executeDrop($parsedStatement, $boundParams, $connection);
            }

            if ($parsedStatement instanceof CreateStatement) {
                return $this->executeCreate($parsedStatement, $boundParams, $connection);
            }
            
            if ($parsedStatement instanceof TruncateStatement) {
                return $this->executeTruncate($parsedStatement, $boundParams, $connection);
            }
            
            if ($parsedStatement instanceof AlterStatement) {
                return $this->executeAlter($parsedStatement, $boundParams, $connection);
            }
            
        } catch (\Exception $e) {
            throw new \RuntimeException("Can't execute query: ".$queryString.", ".$e->getMessage(), 0, $e);
        }
        
        throw new \RuntimeException(
            "Only SELECT, INSERT, SET, DROP, CREATE and TRUNCATE statements are currently supported. Got: "
                .$queryString.". Parsed statement: " . var_export($parsedStatement, true)
        );
    }

    /**
     * @param SelectStatement $selectStatement
     * @param array $boundParams
     * @param MySQLConnection $connection
     * @return MySQLQueryResult
     * @throws \RuntimeException
     */
    protected function executeSelect($selectStatement, $boundParams, MySQLConnection $connection)
    {
        $fromStatement = $selectStatement->from[0];
        
        $databaseName = $fromStatement->database;
        $tableName = $fromStatement->table;
        $tableAlias = $fromStatement->alias;
        $tableObject = $this->getDatabase($databaseName, $connection)->getTable($tableName);
        
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
                $joinedTableObject = $this->getDatabase($joinedDatabaseName, $connection)->getTable($joinedTableName);
                $joinedTableFieldNames = $joinedTableObject->getColumnNames();
                
                try {
                    $joinOnExpressionAST = $this->parser->parse($joinInfoObject->on[0]->expr);
                } catch (\Exception $e) {
                    throw new \RuntimeException(
                        "Can't parse JOIN ON expression: '".$joinInfoObject->on[0]->expr."'", 0, $e);
                }
                
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
        
        try {
            $parsedWhereConditionAST = $this->parser->parse($fullWhereConditionString);
        } catch (\Exception $e) {
            throw new \RuntimeException("Can't parse WHERE condition: ".$fullWhereConditionString, 0, $e);
        }
        
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
        }
        
        $parsedSelectExpressions = [];
        foreach ($selectStatement->expr as $index => $selectExpressionInfo) {
            
            if ("*" == trim($selectExpressionInfo->expr)) {
                foreach ($tableObject->getColumnNames() as $fieldName) {
                    $parsedSelectExpressions[] = [
                        "alias" => $fieldName,
                        "AST" => new TableFieldExpression($fieldName, $tableNameOrAlias, $databaseName),
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
                
                try {
                    $selectExpressionEvaluationResult = $selectExpressionAST->evaluate($evaluationContext);
                    
                    $queryResults[$rowNumber][$selectExpressionAlias] = $selectExpressionEvaluationResult;
                    
                } catch (\Exception $e) {
                    throw new \RuntimeException(
                        "Can't evaluate select expression: ".var_export($selectExpressionAST, true), 0, $e);
                }
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
    protected function evaluateValue($value, &$boundParams)
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
     * @param MySQLConnection $connection
     * @return int|null Last insert ID if ID's were autogenerated
     * @throws \RuntimeException
     */
    protected function insertRow($databaseName, $tableName, $columns, $valueFields, $boundParams,
            MySQLConnection $connection)
    {
        $reindexedValues = [];
        
        foreach ($columns as $columnIndex => $columnName) {
            $value = $valueFields[$columnIndex];
            
            $reindexedValues[$columnName] = $this->evaluateValue($value, $boundParams);
        }
        
        $databaseObject = $this->getDatabase($databaseName, $connection);
        
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
     * @param MySQLConnection $connection
     * @return MySQLQueryResult
     * @throws \RuntimeException
     */
    protected function executeInsert($insertStatement, $boundParams, MySQLConnection $connection)
    {
        $intoStatement = $insertStatement->into;
        
        $databaseName = $intoStatement->dest->database;
        $tableName = $intoStatement->dest->table;
        $columns = $intoStatement->columns;
        
        $values = $insertStatement->values;
        
        $queryResultObject = new MySQLQueryResult();
        $queryResultObject->setAffectedRowsCount(count($values));
        
        foreach ($values as $valueFields) {
            $insertID = $this->insertRow(
                $databaseName, $tableName, $columns, $valueFields->values, $boundParams, $connection);
            
            $queryResultObject->setLastInsertID($insertID);
        }
        
        return $queryResultObject;
    }

    /**
     * @param DropStatement $parsedStatement
     * @param array $boundParams
     * @param MySQLConnection $connection
     * @return MySQLQueryResult
     * @throws \InvalidArgumentException
     */
    protected function executeDrop($parsedStatement, $boundParams, MySQLConnection $connection)
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
     * @param MySQLConnection $connection
     * @return MySQLQueryResult
     * @throws \InvalidArgumentException
     */
    protected function executeCreate($parsedStatement, $boundParams, MySQLConnection $connection)
    {
        $databaseOptionPresent = in_array("DATABASE", $parsedStatement->options->options);
        
        if (!$databaseOptionPresent) {
            return $this->executeCreateTable($parsedStatement, $boundParams, $connection);
        }
        
        
        return $this->executeCreateDatabase($parsedStatement, $boundParams, $connection);
    }

    /**
     * @param string $databaseName
     * @param array $boundParams
     * @param MySQLConnection $connection
     * @return MySQLQueryResult
     * @throws \InvalidArgumentException
     */
    protected function executeUse($databaseName, $boundParams, MySQLConnection $connection)
    {
        $connection->setCurrentDatabaseName($databaseName);
        
        return new MySQLQueryResult();
    }


    /**
     * @param CreateStatement $parsedStatement
     * @param array $boundParams
     * @param MySQLConnection $connection
     * @return MySQLQueryResult
     * @throws \InvalidArgumentException
     */
    protected function executeCreateDatabase($parsedStatement, $boundParams, MySQLConnection $connection)
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
     * @param MySQLConnection $connection
     * @return MySQLQueryResult
     */
    protected function executeCreateTable($parsedStatement, $boundParams, MySQLConnection $connection)
    {
        $ifNotExistsOptionPresent = in_array("IF NOT EXISTS", $parsedStatement->options->options);
        
        $databaseName = $parsedStatement->name->database;
        $tableName = $parsedStatement->name->table;
        
        /** @TODO implement handling these options */
        $entityOptions = $parsedStatement->entityOptions;
        
        if (empty($tableName)) {
            throw new \InvalidArgumentException("Table name in CREATE TABLE must not be empty. Got: ".$tableName);
        }
        
        $databaseObject = $this->getDatabase($databaseName, $connection);
        
        if (!$ifNotExistsOptionPresent && $databaseObject->tableExists($tableName)) {
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

    /**
     * @param TruncateStatement $parsedStatement
     * @param array $boundParams
     * @param MySQLConnection $connection
     * @return MySQLQueryResult
     * @throws \RuntimeException
     */
    protected function executeTruncate($parsedStatement, $boundParams, $connection)
    {
        $databaseName = $parsedStatement->table->database;
        $tableName = $parsedStatement->table->table;
        
        $this->getDatabase($databaseName, $connection)->getTable($tableName)->truncate();
        
        return new MySQLQueryResult();
    }

    /**
     * @param AlterStatement $parsedStatement
     * @param array $boundParams
     * @param MySQLConnection $connection
     */
    protected function executeAlter($parsedStatement, $boundParams, $connection)
    {
        $databaseName = $parsedStatement->table->database;
        $tableName = $parsedStatement->table->table;
        
        $tableObject = $this->getDatabase($databaseName, $connection)->getTable($tableName);
        
        foreach ($parsedStatement->altered as $alterOperation) {
            
            if ("ADD PARTITION" != join(" ", $alterOperation->options->options)) {
                throw new \InvalidArgumentException("Unknown alter statement type: ".var_export($alterOperation, true));
                
            }
            
            // PHPMyAdmin parser doesn't support parsing these. Have to parse manually
            $parsedAddPartitionStatement = $this->parseAddPartitionStatement($alterOperation->unknown);

            $tableObject->addPartition(
                $parsedAddPartitionStatement->partitionName,
                $parsedAddPartitionStatement->partitionValue
            );
        }
    }

    /**
     * @param Token[] $tokens
     * @return ParsedAddPartitionStatement
     */
    protected function parseAddPartitionStatement(array $tokens): ParsedAddPartitionStatement
    {
        // https://dev.mysql.com/doc/refman/5.5/en/alter-table.html:
        //     ADD PARTITION (partition_definition)
        
        // First and last tokens should be parentheses
        
        $firstToken = array_shift($tokens);
        $lastToken = array_pop($tokens);
        
        if ("(" != $firstToken->token || ")" != $lastToken->token) {
            throw new \InvalidArgumentException(
                "Partition definition should be surrounded by parentheses, got: "
                    .$firstToken->value.", ".$lastToken->value
            );
        }
        
        return $this->parsePartitionDefinition($tokens);
    }

    /**
     * @TODO Check if spaces need to be handled more gracefully (if several spaces in a row are allowed, etc).
     * @param Token[] $tokens
     * @return ParsedAddPartitionStatement
     */
    protected function parsePartitionDefinition(array $tokens): ParsedAddPartitionStatement
    {
        // https://dev.mysql.com/doc/refman/5.5/en/create-table.html, see "partition_definition"
        
        $parsedStatement = new ParsedAddPartitionStatement();
        
        $partitionToken = array_shift($tokens);
        $spaceToken = array_shift($tokens);
        
        if ("PARTITION" != $partitionToken->keyword || " " != $spaceToken->token) {
            throw new \InvalidArgumentException(
                "Partition definition should start with 'PARTITION ', got: "
                    .$partitionToken->value.", ".$spaceToken->value
            );
        }
        
        $parsedStatement->partitionName = array_shift($tokens)->value;
        $spaceToken = array_shift($tokens);
        
        if (" " != $spaceToken->token) {
            throw new \InvalidArgumentException(
                "There should be a space after partition name, got: ".$spaceToken->value
            );
        }
        
        $valuesToken = array_shift($tokens);
        $spaceToken = array_shift($tokens);
        $inToken = array_shift($tokens);
        $space2Token = array_shift($tokens);

        if ("VALUES" != $valuesToken->keyword || " " != $spaceToken->token || "IN" != $inToken->keyword
                || " " != $space2Token->token) {
            throw new \InvalidArgumentException(
                "Expected 'VALUES IN' in partition definition, got: "
                . $valuesToken->value . ", " . $spaceToken->value . ", " . $inToken->value . ", " . $space2Token->value
            );
        }
        
        $firstToken = array_shift($tokens);
        $lastToken = array_pop($tokens);
        
        if ("(" != $firstToken->token || ")" != $lastToken->token) {
            throw new \InvalidArgumentException(
                "Parentheses are expected around the IN clause, got: "
                    .$firstToken->value.", ".$lastToken->value
            );
        }
        
        $parsedStatement->partitionValue = array_shift($tokens)->value;
        
        if ($tokens) {
            throw new \InvalidArgumentException("Unsupported ADD PARTITION clauses: ".var_export($tokens, true));
        }
        
        return $parsedStatement;
    }
}
