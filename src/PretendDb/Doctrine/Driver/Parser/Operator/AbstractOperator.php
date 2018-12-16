<?php

namespace PretendDb\Doctrine\Driver\Parser\Operator;


use PretendDb\Doctrine\Driver\Parser\Expression\ExpressionInterface;
use PretendDb\Doctrine\Driver\Parser\Token;

abstract class AbstractOperator
{
    /**
     * @return int
     */
    abstract public function getPrecedence();

    /**
     * @return bool
     */
    abstract public function isUnary();

    /**
     * @return bool
     */
    abstract public function isBinary();

    /**
     * @return bool
     */
    abstract public function isLeftAssociative();

    /**
     * @param Token $token
     * @return bool
     */
    abstract public function matchesToken($token);

    /**
     * @param ExpressionInterface[] $operands
     * @return ExpressionInterface
     */
    abstract public function initAST($operands);
}
