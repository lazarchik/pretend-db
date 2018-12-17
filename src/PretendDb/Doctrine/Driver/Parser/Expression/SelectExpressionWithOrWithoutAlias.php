<?php

namespace PretendDb\Doctrine\Driver\Parser\Expression;

use PretendDb\Doctrine\Driver\Expression\EvaluationContext;

class SelectExpressionWithOrWithoutAlias implements ExpressionInterface
{
    /** @var ExpressionInterface */
    protected $expression;
    
    /** @var string|null */
    protected $alias;

    public function __construct(ExpressionInterface $expression, ?string $alias)
    {
        $this->expression = $expression;
        $this->alias = $alias;
    }
    
    /**
     * @FIXME implement this
     * @param EvaluationContext $evaluationContext
     * @return mixed
     */
    public function evaluate(EvaluationContext $evaluationContext)
    {
        throw new \RuntimeException("Not implemented yet");
    }

    public function dump(string $indentationString = ""): string
    {
        return $this->expression->dump($indentationString).($this->alias ? " AS ".$this->alias : "");
    }
}
