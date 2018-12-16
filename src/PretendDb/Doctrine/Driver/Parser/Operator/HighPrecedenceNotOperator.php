<?php

namespace PretendDb\Doctrine\Driver\Parser\Operator;


use PretendDb\Doctrine\Driver\Parser\Expression\ExpressionInterface;
use PretendDb\Doctrine\Driver\Parser\Expression\NotExpression;
use PretendDb\Doctrine\Driver\Parser\Token;

class HighPrecedenceNotOperator extends AbstractOperator
{
    /**
     * @return int
     */
    public function getPrecedence()
    {
        return 15;
    }

    /**
     * @return bool
     */
    public function isUnary()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isBinary()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isLeftAssociative()
    {
        return false;
    }

    /**
     * @param Token $token
     * @return bool
     */
    public function matchesToken($token)
    {
        return $token->isHighPrecedenceNot();
    }

    /**
     * @param ExpressionInterface[] $operands
     * @return ExpressionInterface
     */
    public function initAST($operands)
    {
        return new NotExpression($operands[0]);
    }
}
