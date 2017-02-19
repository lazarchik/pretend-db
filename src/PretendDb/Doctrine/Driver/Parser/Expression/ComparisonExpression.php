<?php
/**
 * @author: Eugene Lazarchik
 * @date: 2/18/17
 */

namespace PretendDb\Doctrine\Driver\Parser\Expression;


class ComparisonExpression implements ExpressionInterface
{
    /** @var ExpressionInterface */
    protected $leftExpression;
    
    /** @var ExpressionInterface */
    protected $rightExpression;
    
    /** @var string */
    protected $operatorType;

    /**
     * @param string $operatorType
     * @param ExpressionInterface $leftExpression
     * @param ExpressionInterface $rightExpression
     */
    public function __construct($operatorType, $leftExpression, $rightExpression)
    {
        $this->operatorType = $operatorType;
        $this->leftExpression = $leftExpression;
        $this->rightExpression = $rightExpression;
    }

    public function evaluate()
    {
        // TODO: Implement evaluate() method.
    }

    /**
     * @param int|string $indentationString
     * @return string
     */
    public function dump($indentationString = "")
    {
        return $this->operatorType."\n"
            .$indentationString."┣━━ ".$this->leftExpression->dump($indentationString."┃   ")."\n"
            .$indentationString."┗━━ ".$this->rightExpression->dump($indentationString."    ");
    }
}
