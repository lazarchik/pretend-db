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
        switch (strtoupper($this->functionName)) {
            case "IF":
                $this->assertArgumentCount(3);
                return $this->arguments[0]->evaluate($evaluationContext)
                    ? $this->arguments[1]->evaluate($evaluationContext)
                    : $this->arguments[2]->evaluate($evaluationContext);
            case "UPPER":
                $this->assertArgumentCount(1);
                return strtoupper($this->arguments[0]->evaluate($evaluationContext));
            default:
                throw new \RuntimeException("Function ".$this->functionName." is not implemented");
        }
    }
    
    protected function assertArgumentCount(int $expectedArgumentCount): void
    {
        $actualArgumentCount = count($this->arguments);
        if ($expectedArgumentCount != $actualArgumentCount) {
            throw new \RuntimeException(
                "Expected ".$expectedArgumentCount." arguments for function ".$this->functionName
                    .", got: ".$actualArgumentCount
            );
        }
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
