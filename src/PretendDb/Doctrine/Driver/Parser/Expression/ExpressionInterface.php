<?php

namespace PretendDb\Doctrine\Driver\Parser\Expression;


use PretendDb\Doctrine\Driver\Expression\EvaluationContext;

interface ExpressionInterface
{
    /**
     * @param EvaluationContext $evaluationContext
     * @return mixed
     */
    public function evaluate($evaluationContext);

    /**
     * @param string $indentationString
     * @return string
     * @internal param int $indentationLevels
     */
    public function dump($indentationString = "");
}
