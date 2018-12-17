<?php

namespace PretendDb\Doctrine\Driver\Parser\Expression;


use PretendDb\Doctrine\Driver\Expression\EvaluationContext;

class NumberLiteralExpression implements ExpressionInterface
{
    /** @var float */
    protected $value;

    public function __construct(float $value)
    {
        $this->value = $value;
    }

    public function evaluate(EvaluationContext $evaluationContext)
    {
        return $this->value;
    }

    public function dump(string $indentationString = ""): string
    {
        return (string) $this->value;
    }
}
