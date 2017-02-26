<?php
/**
 * @author: Eugene Lazarchik
 * @date: 2/26/17
 */

namespace PretendDb\Doctrine\Driver;


class MySQLQueryResult
{
    /** @var MySQLTable */
    protected $queryResultsTable;
    
    /** @var int|null */
    protected $lastInsertID;
    
    /** @var int|null */
    protected $affectedRowsCount;
    
    /** @var string SQLSTATE error code (a five characters alphanumeric identifier defined in the ANSI SQL standard). */
    protected $sqlStateErrorCode;
    
    /** @var int */
    protected $errorCode;
    
    /** @var string */
    protected $errorMessage;

    public function __construct()
    {
        $this->queryResultsTable = new MySQLTable([]);
        $this->sqlStateErrorCode = "00000";
        $this->errorCode = 0;
        $this->errorMessage = "";
        $this->affectedRowsCount = -1;
        $this->lastInsertID = 0;
    }

    /**
     * @return MySQLTable
     */
    public function getQueryResultsTable()
    {
        return $this->queryResultsTable;
    }

    /**
     * @param MySQLTable $queryResultsTable
     */
    public function setQueryResultsTable(MySQLTable $queryResultsTable)
    {
        $this->queryResultsTable = $queryResultsTable;
    }

    /**
     * @return int|null
     */
    public function getLastInsertID()
    {
        return $this->lastInsertID;
    }

    /**
     * @param int|null $lastInsertID
     */
    public function setLastInsertID($lastInsertID)
    {
        $this->lastInsertID = $lastInsertID;
    }

    /**
     * @return int|null
     */
    public function getAffectedRowsCount()
    {
        return $this->affectedRowsCount;
    }

    /**
     * @param int|null $affectedRowsCount
     */
    public function setAffectedRowsCount($affectedRowsCount)
    {
        $this->affectedRowsCount = $affectedRowsCount;
    }

    /**
     * @return int
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * @param int $errorCode
     */
    public function setErrorCode($errorCode)
    {
        $this->errorCode = $errorCode;
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @param string $errorMessage
     */
    public function setErrorMessage($errorMessage)
    {
        $this->errorMessage = $errorMessage;
    }

    /**
     * @return string
     */
    public function getSqlStateErrorCode()
    {
        return $this->sqlStateErrorCode;
    }

    /**
     * @param string $sqlStateErrorCode
     */
    public function setSqlStateErrorCode($sqlStateErrorCode)
    {
        $this->sqlStateErrorCode = $sqlStateErrorCode;
    }
}
