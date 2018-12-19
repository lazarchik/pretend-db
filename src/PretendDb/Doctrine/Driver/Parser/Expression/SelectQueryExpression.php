<?php

namespace PretendDb\Doctrine\Driver\Parser\Expression;

use PretendDb\Doctrine\Driver\Expression\EvaluationContext;

class SelectQueryExpression extends AbstractExpression
{
    /** @var ExpressionInterface[] */
    protected $selectExpressions;
    
    /** @var TableExpression[] */
    protected $fromExpressions;
    
    /** @var ExpressionInterface|null */
    protected $whereExpression;

    /**
     * @param string $sourceString
     * @param ExpressionInterface[] $selectExpressions
     * @param ExpressionInterface[] $fromExpressions
     * @param ExpressionInterface|null $whereExpression
     */
    public function __construct(
        string $sourceString,
        array $selectExpressions,
        array $fromExpressions,
        ?ExpressionInterface $whereExpression
    ) {
        parent::__construct($sourceString);
        $this->selectExpressions = $selectExpressions;
        $this->fromExpressions = $fromExpressions;
        $this->whereExpression = $whereExpression;
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
        $selectExpressionsDump = "";
        foreach ($this->selectExpressions as $index => $selectExpression) {
            $isLast = count($this->selectExpressions) - 1 == $index
                && !$this->fromExpressions && !$this->whereExpression;
            $prefix = $indentationString.($isLast ? "┗━━ " : "┣━━ ");
            $selectExpressionsDump .= $prefix.$selectExpression->dump($indentationString."┃   ")."\n";
        }
        
        $fromExpressionsDump = "";
        if ($this->fromExpressions) {
            $fromExpressionsDump = $indentationString.($this->whereExpression ? "┣" : "┗")."━━ FROM\n";
            foreach ($this->fromExpressions as $index => $fromExpression) {
                $isLast = count($this->fromExpressions) - 1 == $index;
                $prefix = $indentationString."┃   ".($isLast ? "┗━━ " : "┣━━ ");
                $fromExpressionsDump .= $prefix.$fromExpression->dump($indentationString."┃   ┃   ")."\n";
            }
        }
        
        $whereExpressionDump = "";
        if ($this->whereExpression) {
            $whereExpressionDump = $indentationString."┗━━ WHERE "
                .$this->whereExpression->dump($indentationString."    ");
        }
        
        
        return "SELECT\n".$selectExpressionsDump.$fromExpressionsDump.$whereExpressionDump;
    }
}
