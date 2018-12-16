<?php

namespace PretendDb\Doctrine\Driver\Parser\Operator;


use PretendDb\Doctrine\Driver\Parser\Expression\BinaryArithmeticExpression;
use PretendDb\Doctrine\Driver\Parser\Expression\ExpressionInterface;
use PretendDb\Doctrine\Driver\Parser\Token;

class MultiplicationOperator extends AbstractOperator
{
    /**
     * @return int
     */
    public function getPrecedence()
    {
        return 12;
    }

    /**
     * @return bool
     */
    public function isUnary()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isBinary()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isLeftAssociative()
    {
        return true;
    }

    /**
     * @param Token $token
     * @return bool
     */
    public function matchesToken($token)
    {
        return $token->isMultiplication();
    }

    /**
     * @param ExpressionInterface[] $operands
     * @return ExpressionInterface
     */
    public function initAST($operands)
    {
        return new BinaryArithmeticExpression("*", $operands[0], $operands[1]);
    }
}
