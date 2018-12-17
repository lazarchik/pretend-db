<?php

namespace PretendDb\Doctrine\Driver\Parser\Expression;


use PretendDb\Doctrine\Driver\Expression\EvaluationContext;

class OrExpression implements ExpressionInterface
{
    /** @var ExpressionInterface */
    protected $leftOperand;
    
    /** @var ExpressionInterface */
    protected $rightOperand;

    public function __construct(ExpressionInterface $leftOperand, ExpressionInterface $rightOperand)
    {
        $this->leftOperand = $leftOperand;
        $this->rightOperand = $rightOperand;
    }

    public function evaluate(EvaluationContext $evaluationContext)
    {
        return $this->leftOperand->evaluate($evaluationContext) || $this->rightOperand->evaluate($evaluationContext);
    }

    public function dump(string $indentationString = ""): string
    {
        return "OR\n"
            .$indentationString."┣━━ ".$this->leftOperand->dump($indentationString."┃   ")."\n"
            .$indentationString."┗━━ ".$this->rightOperand->dump($indentationString."    ");
    }
}
