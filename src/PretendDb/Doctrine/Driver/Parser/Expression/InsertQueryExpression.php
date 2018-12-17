<?php

namespace PretendDb\Doctrine\Driver\Parser\Expression;

use PretendDb\Doctrine\Driver\Expression\EvaluationContext;

class InsertQueryExpression implements ExpressionInterface
{
    /** @var string */
    protected $tableName;
    
    /** @var string[] */
    protected $fieldNames;
    
    /** @var ExpressionInterface[][] */
    protected $valuesLists;

    /**
     * @param string $tableName
     * @param string[] $fieldNames
     * @param ExpressionInterface[][] $valuesLists
     */
    public function __construct(string $tableName, array $fieldNames, array $valuesLists)
    {
        $this->tableName = $tableName;
        $this->fieldNames = $fieldNames;
        $this->valuesLists = $valuesLists;
    }
    
    /**
     * @FIXME implement this
     * @param EvaluationContext $evaluationContext
     * @return mixed[][]
     */
    public function evaluate(EvaluationContext $evaluationContext): array
    {
        $fieldValues = [];
        foreach ($this->valuesLists as $valuesListIndex => $valuesList) {
            $fieldValues[$valuesListIndex] = [];
            foreach ($valuesList as $fieldIndex => $fieldValueExpression) {
                if (!array_key_exists($fieldIndex, $this->fieldNames)) {
                    throw new \RuntimeException(
                        "Values number doesn't match field number ("
                            .count($valuesList)." vs ".count($this->fieldNames).")"
                    );
                }
                
                $fieldName = $this->fieldNames[$fieldIndex];
                $fieldValues[$valuesListIndex][$fieldName] = $fieldValueExpression->evaluate($evaluationContext);
            }
        }
        
        return $fieldValues;
    }

    public function dump(string $indentationString = ""): string
    {
        $valuesExpressionsDump = "";
        foreach ($this->valuesLists as $valuesListIndex => $valuesList) {
            $isLastValuesList = count($this->valuesLists) - 1 == $valuesListIndex;
            $valuesExpressionsDump = $indentationString.($isLastValuesList ? "┗━━ " : "┣━━ ").$valuesListIndex."\n";
            
            foreach ($valuesList as $valueIndex => $value) {
                $isLastValue = count($valuesList) - 1 == $valueIndex;
                $valuePrefix = $indentationString."    ".($isLastValue ? "┗━━ " : "┣━━ ");
                $valuesExpressionsDump .= $valuePrefix.$value->dump($indentationString."    ")."\n";
            }
        }
        
        return "INSERT INTO ".$this->tableName." (".join(", ", $this->fieldNames).")\n".$valuesExpressionsDump;
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * @return string[]
     */
    public function getFieldNames(): array
    {
        return $this->fieldNames;
    }
}
