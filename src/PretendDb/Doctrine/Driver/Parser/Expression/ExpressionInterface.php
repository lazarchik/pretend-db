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

    /**
     * Default select expression alias to be used when no explicit alias is present.
     * If expression is a table field expression, this is going to be just field name.
     * Otherwise it's going to be the expression's source string.
     * @return string
     */
    public function getDefaultAlias(): string;
}
