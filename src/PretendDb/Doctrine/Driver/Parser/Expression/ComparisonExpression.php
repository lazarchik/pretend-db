<?php

namespace PretendDb\Doctrine\Driver\Parser\Expression;


use PretendDb\Doctrine\Driver\Expression\EvaluationContext;

class ComparisonExpression implements ExpressionInterface
{
    /** @var string */
    protected $operatorType;
    
    /** @var ExpressionInterface */
    protected $leftExpression;
    
    /** @var ExpressionInterface */
    protected $rightExpression;

    public function __construct(
        string $operatorType,
        ExpressionInterface $leftExpression,
        ExpressionInterface $rightExpression
    ) {
        $this->operatorType = $operatorType;
        $this->leftExpression = $leftExpression;
        $this->rightExpression = $rightExpression;
    }

    public function evaluate(EvaluationContext $evaluationContext)
    {
        $leftExpressionResult = $this->leftExpression->evaluate($evaluationContext);
        $rightExpressionResult = $this->rightExpression->evaluate($evaluationContext);
        
        switch ($this->operatorType) {
            case "=": return $leftExpressionResult == $rightExpressionResult;
            case "!=": return $leftExpressionResult != $rightExpressionResult;
            case ">": return $leftExpressionResult > $rightExpressionResult;
            case ">=": return $leftExpressionResult >= $rightExpressionResult;
            case "<": return $leftExpressionResult < $rightExpressionResult;
            case "<=": return $leftExpressionResult <= $rightExpressionResult;
            default:
                throw new \RuntimeException("Invalid comparison operator type: ".$this->operatorType);
        }
    }

    public function dump(string $indentationString = ""): string
    {
        return $this->operatorType."\n"
            .$indentationString."┣━━ ".$this->leftExpression->dump($indentationString."┃   ")."\n"
            .$indentationString."┗━━ ".$this->rightExpression->dump($indentationString."    ");
    }
}
