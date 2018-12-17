<?php

namespace PretendDb\Doctrine\Driver\Parser\Expression;


use PretendDb\Doctrine\Driver\Expression\EvaluationContext;

class FunctionCallExpression implements ExpressionInterface
{
    /** @var string */
    protected $functionName;
    
    /** @var ExpressionInterface[] */
    protected $arguments;

    public function __construct(string $functionName, array $arguments)
    {
        $this->functionName = $functionName;
        $this->arguments = $arguments;
    }
    
    public function evaluate(EvaluationContext $evaluationContext)
    {
        // TODO: Implement evaluate() method.
        //throw new \RuntimeException("Method not implemented");
        
        return 1;
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
