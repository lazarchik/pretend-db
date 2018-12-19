<?php

namespace PretendDb\Doctrine\Driver\Parser\Expression;


use PretendDb\Doctrine\Driver\Expression\EvaluationContext;

class InExpression extends AbstractExpression
{
    /** @var ExpressionInterface */
    protected $leftOperand;
    
    /** @var ExpressionInterface[] */
    protected $expressionListOnTheRight;

    /**
     * @param string $sourceString
     * @param ExpressionInterface $leftOperand
     * @param ExpressionInterface[] $expressionListOnTheRight
     */
    public function __construct(string $sourceString, ExpressionInterface $leftOperand, array $expressionListOnTheRight)
    {
        parent::__construct($sourceString);
        $this->leftOperand = $leftOperand;
        $this->expressionListOnTheRight = $expressionListOnTheRight;
    }

    public function evaluate(EvaluationContext $evaluationContext)
    {
        $evaluatedExpressions = [];
        foreach ($this->expressionListOnTheRight as $expression) {
            $evaluatedExpressions[] = $expression->evaluate($evaluationContext);
        }
        
        return \in_array($this->leftOperand->evaluate($evaluationContext), $evaluatedExpressions);
    }

    public function dump(string $indentationString = ""): string
    {
        $expressionsOnTheRightDump = "";
        foreach ($this->expressionListOnTheRight as $index => $expression) {
            $isLastValue = count($this->expressionListOnTheRight) - 1 == $index;
            $prefix = $indentationString.($isLastValue ? "┗━━ " : "┣━━ ");
            $expressionsOnTheRightDump .= $prefix.$expression->dump($indentationString."    ")."\n";
        }
        
        return "IN\n"
            .$indentationString."┣━━ ".$this->leftOperand->dump($indentationString."┃   ")."\n"
            .$expressionsOnTheRightDump;
    }
}
