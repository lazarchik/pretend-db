<?php

namespace PretendDb\Doctrine\Driver\Parser\Expression;


use PretendDb\Doctrine\Driver\Expression\EvaluationContext;

class SimplePlaceholderExpression implements ExpressionInterface
{
    public function evaluate(EvaluationContext $evaluationContext)
    {
        return $evaluationContext->extractOneBoundParam();
    }

    /**
     * @param string $indentationString
     * @return string
     */
    public function dump(string $indentationString = ""): string
    {
        return "?";
    }
}
