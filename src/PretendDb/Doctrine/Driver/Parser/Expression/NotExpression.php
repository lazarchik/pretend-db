<?php
/**
 * @author: Eugene Lazarchik
 * @date: 2/18/17
 */

namespace PretendDb\Doctrine\Driver\Parser\Expression;


class NotExpression implements ExpressionInterface
{
    /** @var ExpressionInterface */
    protected $operand;

    /**
     * @param ExpressionInterface $operand
     */
    public function __construct($operand)
    {
        $this->operand = $operand;
    }

    public function evaluate($evaluationContext)
    {
        return !$this->operand->evaluate($evaluationContext);
    }

    /**
     * @param string $indentationString
     * @return string
     */
    public function dump($indentationString = "")
    {
        return "NOT\n" . $indentationString . "┗━━ " . $this->operand->dump($indentationString . "    ");
    }
}
