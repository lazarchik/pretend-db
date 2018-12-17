<?php

namespace PretendDb\Doctrine\Driver\Parser\Expression;


use PretendDb\Doctrine\Driver\Expression\EvaluationContext;

class StringLiteralExpression implements ExpressionInterface
{
    /** @var string */
    protected $value;

    public function __construct(string $value)
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
