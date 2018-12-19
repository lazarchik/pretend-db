<?php

namespace PretendDb\Doctrine\Driver\Parser\Expression;


use PretendDb\Doctrine\Driver\Expression\EvaluationContext;

class CurrentTimestampExpression extends AbstractExpression
{
    public function __construct(string $sourceString)
    {
        parent::__construct($sourceString);
    }

    public function evaluate(EvaluationContext $evaluationContext)
    {
        /** @TODO: Implement returning a string or an integer based on expected data type */
        return time();
    }

    public function dump(string $indentationString = ""): string
    {
        return "CURRENT_TIMESTAMP";
    }
}
