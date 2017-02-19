<?php
/**
 * @author: Eugene Lazarchik
 * @date: 2/18/17
 */

namespace PretendDb\Doctrine\Driver\Parser\Expression;


class AndExpression implements ExpressionInterface
{
    /** @var ExpressionInterface */
    protected $leftOperand;
    
    /** @var ExpressionInterface */
    protected $rightOperand;

    /**
     * @param ExpressionInterface $leftOperand
     * @param ExpressionInterface $rightOperand
     */
    public function __construct($leftOperand, $rightOperand)
    {
        $this->leftOperand = $leftOperand;
        $this->rightOperand = $rightOperand;
    }

    public function evaluate($evaluationContext)
    {
        return $this->leftOperand->evaluate($evaluationContext) && $this->rightOperand->evaluate($evaluationContext);
    }

    /**
     * @param string $indentationString
     * @return string
     */
    public function dump($indentationString = "")
    {
        return "AND\n"
            .$indentationString."┣━━ ".$this->leftOperand->dump($indentationString."┃   ")."\n"
            .$indentationString."┗━━ ".$this->rightOperand->dump($indentationString ."    ");
    }
}
