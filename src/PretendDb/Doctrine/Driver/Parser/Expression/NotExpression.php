<?php

namespace PretendDb\Doctrine\Driver\Parser\Expression;


use PretendDb\Doctrine\Driver\Expression\EvaluationContext;

class NotExpression extends AbstractExpression
{
    /** @var ExpressionInterface */
    protected $operand;

    public function __construct(string $sourceString, ExpressionInterface $operand)
    {
        parent::__construct($sourceString);
        $this->operand = $operand;
    }

    public function evaluate(EvaluationContext $evaluationContext)
    {
        return !$this->operand->evaluate($evaluationContext);
    }

    public function dump(string $indentationString = ""): string
    {
        return "NOT\n" . $indentationString . "┗━━ " . $this->operand->dump($indentationString . "    ");
    }
}
