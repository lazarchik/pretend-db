<?php

namespace PretendDb\Doctrine\Driver\Parser\Expression;


use PretendDb\Doctrine\Driver\Expression\EvaluationContext;

interface ExpressionInterface
{
    /**
     * @param EvaluationContext $evaluationContext
     * @return mixed
     */
    public function evaluate(EvaluationContext $evaluationContext);

    public function dump(string $indentationString = ""): string;
}
