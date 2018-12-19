<?php

namespace PretendDb\Doctrine\Driver\Parser\Expression;


use PretendDb\Doctrine\Driver\Expression\EvaluationContext;

class NumberLiteralExpression extends AbstractExpression
{
    /** @var float */
    protected $value;

    public function __construct(string $sourceString, float $value)
    {
        parent::__construct($sourceString);
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
