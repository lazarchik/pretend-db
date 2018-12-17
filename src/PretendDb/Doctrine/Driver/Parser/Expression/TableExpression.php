<?php

namespace PretendDb\Doctrine\Driver\Parser\Expression;


use PretendDb\Doctrine\Driver\Expression\EvaluationContext;

class TableExpression implements ExpressionInterface
{
    /** @var string|null */
    protected $databaseName;
    
    /** @var string */
    protected $tableName;
    
    /** @var string|null */
    protected $alias;

    public function __construct(string $tableName, ?string $databaseName, ?string $alias)
    {
        $this->tableName = $tableName;
        $this->databaseName = $databaseName;
        $this->alias = $alias;
    }

    public function evaluate(EvaluationContext $evaluationContext)
    {
        throw new \RuntimeException("Not implemented yet");
    }

    public function dump(string $indentationString = ""): string
    {
        return
            (null === $this->databaseName ? "" : $this->databaseName.".")
            .$this->tableName
            .(null === $this->alias ? "" : " AS ".$this->alias);
    }
}
