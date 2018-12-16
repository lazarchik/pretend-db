<?php

namespace PretendDb\Doctrine\Driver\Parser\Expression;


class SimplePlaceholderExpression implements ExpressionInterface
{
    public function evaluate($evaluationContext)
    {
        return $evaluationContext->extractOneBoundParam();
    }

    /**
     * @param string $indentationString
     * @return string
     */
    public function dump($indentationString = "")
    {
        return "?";
    }
}
