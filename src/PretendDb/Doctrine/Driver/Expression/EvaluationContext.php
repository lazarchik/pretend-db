<?php

namespace PretendDb\Doctrine\Driver\Expression;


class EvaluationContext
{
    /**
     * Field values organized in the way that lets quickly determine if there's ambiguity in field naming.
     * @var array ["field" => ["tableOrAlias" => ["database" => "value"]]]
     */
    protected $fieldValues = [];
    
    /** @var array ["table_alias" => "table_name"] */
    protected $tableAliases = [];
    
    /** @var array */
    protected $boundParamValues = [];

    /**
     * @param array $boundParamValues
     */
    public function setBoundParamValues($boundParamValues)
    {
        $this->boundParamValues = $boundParamValues;
    }

    /**
     * @param string $databaseName
     * @param string $tableNameOrAlias
     * @param array $rowFields
     */
    public function setTableRow($databaseName, $tableNameOrAlias, $rowFields)
    {
        foreach ($rowFields as $fieldName => $fieldValue) {
            $this->fieldValues[$fieldName][$tableNameOrAlias][$databaseName] = $fieldValue;
        }
    }

    /**
     * @param string $tableAlias
     * @param string $tableName
     */
    public function addTableAlias($tableAlias, $tableName)
    {
        if (array_key_exists($tableAlias, $this->tableAliases)) {
            throw new \RuntimeException("Table alias is not unique: ".$tableAlias);
        }
        
        $this->tableAliases[$tableAlias] = $tableName;
    }

    /**
     * @param string[] $tableAliases
     */
    public function addTableAliases($tableAliases)
    {
        foreach ($tableAliases as $tableAlias => $tableName) {
            $this->addTableAlias($tableAlias, $tableName);
        }
    }

    /**
     * @return mixed
     */
    public function extractOneBoundParam()
    {
        return array_shift($this->boundParamValues);
    }

    /**
     * @param string $fieldName
     * @param string|null $tableNameOrAlias
     * @param string|null $databaseName
     * @return mixed
     * @throws \RuntimeException
     */
    public function getFieldValue($fieldName, $tableNameOrAlias, $databaseName)
    {
        if (!array_key_exists($fieldName, $this->fieldValues)) {
            throw new \RuntimeException(
                "Unknown field: ".$fieldName . ". Known fields: ".implode(", ", array_keys($this->fieldValues)));
        }
        
        // If table name or alias is not specified, make sure there's no ambiguity
        if (null === $tableNameOrAlias) {
            
            if (count($this->fieldValues[$fieldName]) > 1) {
                
                // Need to throw an exception. Let's prepare a list of tables that create ambiguity
                
                $tableOptions = [];
                foreach ($this->fieldValues[$fieldName] as $tableNameOption => $databaseNameAndFieldValueArray) {
                    foreach ($databaseNameAndFieldValueArray as $databaseNameOption => $fieldValue) {
                        $tableOptions[] = $databaseNameOption.".".$tableNameOption;
                    }
                }
                
                throw new \RuntimeException("Field ".$fieldName." is ambiguous. Can't decide between tables: "
                    . join(", ", $tableOptions));
            }
            
            $tableNameOrAlias = key($this->fieldValues[$fieldName]);
            
        } elseif (!array_key_exists($tableNameOrAlias, $this->fieldValues[$fieldName])) {
            throw new \RuntimeException("Unknown field: ".$tableNameOrAlias.".".$fieldName);
        }
        
        // If database name is not specified, make sure there's no ambiguity
        if (null === $databaseName) {
            
            if (count($this->fieldValues[$fieldName][$tableNameOrAlias]) > 1) {
                throw new \RuntimeException(
                    "Field ".$tableNameOrAlias.".".$fieldName." is ambiguous. Can't decide between: "
                    . join(", ", array_keys($this->fieldValues[$fieldName][$tableNameOrAlias])));
            }
            
            $databaseName = key($this->fieldValues[$fieldName][$tableNameOrAlias]);
            
        } elseif (!array_key_exists($databaseName, $this->fieldValues[$fieldName][$tableNameOrAlias])) {
            throw new \RuntimeException("Unknown field: ".$databaseName.".".$tableNameOrAlias.".".$fieldName);
        }
        
        return $this->fieldValues[$fieldName][$tableNameOrAlias][$databaseName];
    }
}
