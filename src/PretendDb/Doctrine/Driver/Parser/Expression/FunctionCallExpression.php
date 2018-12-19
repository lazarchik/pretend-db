<?php

namespace PretendDb\Doctrine\Driver\Parser\Expression;


use PretendDb\Doctrine\Driver\Expression\EvaluationContext;

class FunctionCallExpression extends AbstractExpression
{
    /** @var string */
    protected $functionName;
    
    /** @var ExpressionInterface[] */
    protected $arguments;

    public function __construct(string $sourceString, string $functionName, array $arguments)
    {
        parent::__construct($sourceString);
        $this->functionName = $functionName;
        $this->arguments = $arguments;
    }
    
    public function evaluate(EvaluationContext $evaluationContext)
    {
        throw new \RuntimeException("Method not implemented");
    }

    public function dump(string $indentationString = ""): string
    {
        $argumentDumps = [];
        foreach ($this->arguments as $argument) {
            $argumentDumps[] = $argument->dump($indentationString);
        }
        
        return $this->functionName."(".join(", ", $argumentDumps).")";
    }
}
