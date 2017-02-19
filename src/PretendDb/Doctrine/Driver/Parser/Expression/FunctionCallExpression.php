<?php
/**
 * @author: Eugene Lazarchik
 * @date: 2/18/17
 */

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
    
    public function evaluate()
    {
        // TODO: Implement evaluate() method.
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
