<?php

namespace PretendDb\Doctrine\Driver\Parser\Expression;


use PretendDb\Doctrine\Driver\Expression\EvaluationContext;

class AndExpression extends AbstractExpression
{
    /** @var ExpressionInterface */
    protected $leftOperand;
    
    /** @var ExpressionInterface */
    protected $rightOperand;

    public function __construct(
        string $sourceString,
        ExpressionInterface $leftOperand,
        ExpressionInterface $rightOperand
    ) {
        parent::__construct($sourceString);
        $this->leftOperand = $leftOperand;
        $this->rightOperand = $rightOperand;
    }

    public function evaluate(EvaluationContext $evaluationContext)
    {
        return $this->leftOperand->evaluate($evaluationContext) && $this->rightOperand->evaluate($evaluationContext);
    }

    public function dump(string $indentationString = ""): string
    {
        return "AND\n"
            .$indentationString."┣━━ ".$this->leftOperand->dump($indentationString."┃   ")."\n"
            .$indentationString."┗━━ ".$this->rightOperand->dump($indentationString ."    ");
    }
}
