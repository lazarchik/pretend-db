<?php

namespace PretendDb\Doctrine\Driver\Parser\Expression;


class TableFieldExpression implements ExpressionInterface
{
    /** @var string|null */
    protected $databaseName;
    
    /** @var string|null */
    protected $tableName;
    
    /** @var string */
    protected $fieldName;

    /**
     * @param string $fieldName
     * @param string|null $tableName
     * @param string|null $databaseName
     */
    public function __construct($fieldName, $tableName, $databaseName)
    {
        $this->fieldName = $fieldName;
        $this->tableName = $tableName;
        $this->databaseName = $databaseName;
    }

    public function evaluate($evaluationContext)
    {
        return $evaluationContext->getFieldValue($this->fieldName, $this->tableName, $this->databaseName);
    }

    /**
     * @param string $indentationString
     * @return string
     */
    public function dump($indentationString = "")
    {
        return
            (null === $this->databaseName ? "" : $this->databaseName.".")
            .(null === $this->tableName ? "" : $this->tableName.".")
            .$this->fieldName;
    }
}
