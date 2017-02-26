<?php
/**
 * @author: Eugene Lazarchik
 * @date: 2/18/17
 */

namespace PretendDb\Doctrine\Driver\Parser\Expression;


class StringLiteralExpression implements ExpressionInterface
{
    /** @var string */
    protected $value;

    /**
     * @param string $value
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
