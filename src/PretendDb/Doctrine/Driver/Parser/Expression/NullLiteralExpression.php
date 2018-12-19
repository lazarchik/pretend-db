<?php

namespace PretendDb\Doctrine\Driver\Parser\Expression;


use PretendDb\Doctrine\Driver\Expression\EvaluationContext;

class NullLiteralExpression extends AbstractExpression
{
    public function __construct(string $sourceString)
    {
        parent::__construct($sourceString);
    }

    public function evaluate(EvaluationContext $evaluationContext)
    {
        return null;
    }

    public function dump(string $indentationString = ""): string
    {
        return "NULL";
    }
}
