<?php

namespace PretendDb\Doctrine\Driver\Parser\Expression;

abstract class AbstractExpression implements ExpressionInterface
{
    /** @var string */
    protected $sourceString;

    public function __construct(string $sourceString)
    {
        $this->sourceString = $sourceString;
    }

    /**
     * Default select expression alias to be used when no explicit alias is present.
     * If expression is a table field expression, this is going to be just field name.
     * Otherwise it's going to be the expression's source string.
     * @return string
     */
    public function getDefaultAlias(): string
    {
        return $this->sourceString;
    }
}
