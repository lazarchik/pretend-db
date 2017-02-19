<?php
/**
 * @author: Eugene Lazarchik
 * @date: 7/7/16
 */

namespace PretendDb\Doctrine\Driver;


use Doctrine\DBAL\Driver\Statement;
use PhpMyAdmin\SqlParser\Components\Condition;
use PhpMyAdmin\SqlParser\Statements\InsertStatement;
use PhpMyAdmin\SqlParser\Statements\SelectStatement;
use PretendDb\Doctrine\Driver\Parser\Lexer;
use PretendDb\Doctrine\Driver\Parser\Parser;

class MySQLStatement implements \IteratorAggregate, Statement
{
    /** @var MySQLStorage */
    protected $storage;
    
    /** @var string */
    protected $queryString;
    
    /** @var array */
    protected $boundParams = [];
    
    /**
     * @param MySQLStorage $objStorage
     * @param string $objQueryString
     */
    public function __construct(MySQLStorage $objStorage, $objQueryString)
    {
        $this->storage = $objStorage;
        $this->queryString = $objQueryString;
    }

    /**
     * Closes the cursor, enabling the statement to be executed again.
     *
     * @return boolean TRUE on success or FALSE on failure.
     */
    public function closeCursor()
    {
        // TODO: Implement closeCursor() method.
        return true;
    }

    /**
     * Returns the number of columns in the result set
     *
     * @return integer The number of columns in the result set represented
     *                 by the PDOStatement object. If there is no result set,
     *                 this method should return 0.
     */
    public function columnCount()
    {
        // TODO: Implement columnCount() method.
        throw new \RuntimeException("Not implemented yet");
    }

    /**
     * Sets the fetch mode to use while iterating this statement.
     *
     * @param integer $fetchMode The fetch mode must be one of the PDO::FETCH_* constants.
     * @param mixed $arg2
     * @param mixed $arg3
     *
     * @return boolean
     *
     * @see PDO::FETCH_* constants.
     */
    public function setFetchMode($fetchMode, $arg2 = null, $arg3 = null)
    {
        // TODO: Implement setFetchMode() method.
        return true;
    }

    /**
     * Returns the next row of a result set.
     *
     * @param integer|null $fetchMode Controls how the next row will be returned to the caller.
     *                                The value must be one of the PDO::FETCH_* constants,
     *                                defaulting to PDO::FETCH_BOTH.
     *
     * @return mixed The return value of this method on success depends on the fetch mode. In all cases, FALSE is
     *               returned on failure.
     *
     * @see PDO::FETCH_* constants.
     */
    public function fetch($fetchMode = null)
    {
        var_dump("\fetching");
        // TODO: Implement fetch() method.
        return false;
    }

    /**
     * Returns an array containing all of the result set rows.
     *
     * @param integer|null $fetchMode Controls how the next row will be returned to the caller.
     *                                The value must be one of the PDO::FETCH_* constants,
     *                                defaulting to PDO::FETCH_BOTH.
     *
     * @return array
     *
     * @see PDO::FETCH_* constants.
     */
    public function fetchAll($fetchMode = null)
    {
        // TODO: Implement fetchAll() method.
        throw new \RuntimeException("Not implemented yet");
    }

    /**
     * Returns a single column from the next row of a result set or FALSE if there are no more rows.
     *
     * @param integer $columnIndex 0-indexed number of the column you wish to retrieve from the row.
     *                             If no value is supplied, PDOStatement->fetchColumn()
     *                             fetches the first column.
     *
     * @return string|boolean A single column in the next row of a result set, or FALSE if there are no more rows.
     */
    public function fetchColumn($columnIndex = 0)
    {
        // TODO: Implement fetchColumn() method.
        throw new \RuntimeException("Not implemented yet");
    }

    /**
     * Binds a value to a corresponding named (not supported by mysqli driver, see comment below) or positional
     * placeholder in the SQL statement that was used to prepare the statement.
     *
     * As mentioned above, the named parameters are not natively supported by the mysqli driver, use executeQuery(),
     * fetchAll(), fetchArray(), fetchColumn(), fetchAssoc() methods to have the named parameter emulated by doctrine.
     *
     * @param mixed $param Parameter identifier. For a prepared statement using named placeholders,
     *                       this will be a parameter name of the form :name. For a prepared statement
     *                       using question mark placeholders, this will be the 1-indexed position of the parameter.
     * @param mixed $value The value to bind to the parameter.
     * @param integer $type Explicit data type for the parameter using the PDO::PARAM_* constants.
     *
     * @return boolean TRUE on success or FALSE on failure.
     */
    public function bindValue($param, $value, $type = null)
    {
        echo "bindValue($param, $value, $type)\n";
        
        $this->boundParams[$param] = $value;
        
        // TODO: Implement bindValue() method.
        return true;
    }

    /**
     * Binds a PHP variable to a corresponding named (not supported by mysqli driver, see comment below) or question
     * mark placeholder in the SQL statement that was use to prepare the statement. Unlike PDOStatement->bindValue(),
     * the variable is bound as a reference and will only be evaluated at the time
     * that PDOStatement->execute() is called.
     *
     * As mentioned above, the named parameters are not natively supported by the mysqli driver, use executeQuery(),
     * fetchAll(), fetchArray(), fetchColumn(), fetchAssoc() methods to have the named parameter emulated by doctrine.
     *
     * Most parameters are input parameters, that is, parameters that are
     * used in a read-only fashion to build up the query. Some drivers support the invocation
     * of stored procedures that return data as output parameters, and some also as input/output
     * parameters that both send in data and are updated to receive it.
     *
     * @param mixed $column Parameter identifier. For a prepared statement using named placeholders,
     *                               this will be a parameter name of the form :name. For a prepared statement using
     *                               question mark placeholders, this will be the 1-indexed position of the parameter.
     * @param mixed $variable Name of the PHP variable to bind to the SQL statement parameter.
     * @param integer|null $type Explicit data type for the parameter using the PDO::PARAM_* constants. To return
     *                               an INOUT parameter from a stored procedure, use the bitwise OR operator to set the
     *                               PDO::PARAM_INPUT_OUTPUT bits for the data_type parameter.
     * @param integer|null $length You must specify maxlength when using an OUT bind
     *                               so that PHP allocates enough memory to hold the returned value.
     *
     * @return boolean TRUE on success or FALSE on failure.
     */
    public function bindParam($column, &$variable, $type = null, $length = null)
    {
        // TODO: Implement bindParam() method.
        throw new \RuntimeException("Not implemented yet");
    }

    /**
     * Fetches the SQLSTATE associated with the last operation on the statement handle.
     *
     * @see Doctrine_Adapter_Interface::errorCode()
     *
     * @return string The error code string.
     */
    public function errorCode()
    {
        // TODO: Implement errorCode() method.
        throw new \RuntimeException("Not implemented yet");
    }

    /**
     * Fetches extended error information associated with the last operation on the statement handle.
     *
     * @see Doctrine_Adapter_Interface::errorInfo()
     *
     * @return array The error info array.
     */
    public function errorInfo()
    {
        // TODO: Implement errorInfo() method.
        throw new \RuntimeException("Not implemented yet");
    }

    /**
     * @param Condition $conditionStatement
     * @return string
     */
    protected function parseCondition($conditionStatement)
    {
        return $conditionStatement->expr;
    }

    /**
     * @param SelectStatement $selectStatement
     * @return bool
     * @throws \RuntimeException
     */
    protected function prepareSelect($selectStatement)
    {
        $fromStatement = $selectStatement->from[0];
        
        $table = $fromStatement->table;
        $tableAlias = $fromStatement->alias;
        
        $conditionStrings = [];
        foreach ($selectStatement->where as $condition) {
            $conditionStrings[] = $this->parseCondition($condition);
        }
        
        $fullCondition = join(" ", $conditionStrings);
        
        $parser = new Parser(new Lexer());
        
        $parser->parse($fullCondition);
        
        return true;
    }

    /**
     * @param string $value
     * @return mixed
     */
    protected function evaluateValue($value)
    {
        if ("?" == $value) {
            return array_shift($this->boundParams);
        }
        
        return $value;
    }

    /**
     * @param string $tableName
     * @param string[] $columns
     * @param array $valueFields
     */
    protected function insertRow($tableName, $columns, $valueFields)
    {
        $reindexedValues = [];
        
        foreach ($columns as $columnIndex => $columnName) {
            $value = $valueFields[$columnIndex];
            
            $reindexedValues[$columnName] = $this->evaluateValue($value);
        }
        
        $storageTable = $this->storage->getTable($tableName);
        
        $storageTable->insertRow($reindexedValues);
    }

    /**
     * @param InsertStatement $insertStatement
     * @return bool
     */
    protected function prepareInsert($insertStatement)
    {
        $intoStatement = $insertStatement->into;
        
        $tableName = $intoStatement->dest->table;
        $columns = $intoStatement->columns;
        
        $values = $insertStatement->values;
        
        foreach ($values as $valueFields) {
            $this->insertRow($tableName, $columns, $valueFields->values);
        }
        
        return true;
    }

    /**
     * Executes a prepared statement
     *
     * If the prepared statement included parameter markers, you must either:
     * call PDOStatement->bindParam() to bind PHP variables to the parameter markers:
     * bound variables pass their value as input and receive the output value,
     * if any, of their associated parameter markers or pass an array of input-only
     * parameter values.
     *
     *
     * @param array|null $params An array of values with as many elements as there are
     *                           bound parameters in the SQL statement being executed.
     *
     * @return boolean TRUE on success or FALSE on failure.
     */
    public function execute($params = null)
    {
        echo "execute with query \"".$this->queryString."\" with params:\n".var_export($this->boundParams, true)."\n";
        
        $parser = new \PhpMyAdmin\SqlParser\Parser($this->queryString);
        
        $parsedStatement = $parser->statements[0];
        
        if ($parsedStatement instanceof SelectStatement) {
            return $this->prepareSelect($parsedStatement);
        }
        
        if ($parsedStatement instanceof InsertStatement) {
            return $this->prepareInsert($parsedStatement);
        }
        
        return true;
    }

    /**
     * Returns the number of rows affected by the last DELETE, INSERT, or UPDATE statement
     * executed by the corresponding object.
     *
     * If the last SQL statement executed by the associated Statement object was a SELECT statement,
     * some databases may return the number of rows returned by that statement. However,
     * this behaviour is not guaranteed for all databases and should not be
     * relied on for portable applications.
     *
     * @return integer The number of rows.
     */
    public function rowCount()
    {
        // TODO: Implement rowCount() method.
        throw new \RuntimeException("Not implemented yet");
    }
    
    public function getIterator()
    {
        // TODO: Implement this
        return new \ArrayIterator([]);
    }
}
