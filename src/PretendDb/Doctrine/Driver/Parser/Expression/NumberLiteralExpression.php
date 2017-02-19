<?php
/**
 * @author: Eugene Lazarchik
 * @date: 2/18/17
 */

namespace PretendDb\Doctrine\Driver\Parser\Expression;


class NumberLiteralExpression implements ExpressionInterface
{
    /** @var float */
    protected $value;

    /**
     * @param float $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    public function evaluate($evaluationContext)
    {
        return $this->value;
    }

    /**
     * @param string $indentationString
     * @return string
     */
    public function dump($indentationString = "")
    {
        return (string) $this->value;
    }
}
