<?php

namespace PretendDb\Doctrine\Driver\Parser\Expression;


class FunctionCallExpression implements ExpressionInterface
{
    /** @var string */
    protected $functionName;
    
    /** @var ExpressionInterface[] */
    protected $arguments;

    /**
     * @param string $functionName
     * @param ExpressionInterface[]$arguments
     */
    public function __construct($functionName, $arguments)
    {
        $this->functionName = $functionName;
        $this->arguments = $arguments;
    }
    
    public function evaluate($evaluationContext)
    {
        // TODO: Implement evaluate() method.
        //throw new \RuntimeException("Method not implemented");
        
        return 1;
    }

    /**
     * @param string $indentationString
     * @return string
     */
    public function dump($indentationString = "")
    {
        $argumentDumps = [];
        foreach ($this->arguments as $argument) {
            $argumentDumps[] = $argument->dump($indentationString);
        }
        
        return $this->functionName."(".join(", ", $argumentDumps).")";
    }
}
