<?php
/**
 * @author: Eugene Lazarchik
 * @date: 2/21/17
 */

namespace PretendDb\Doctrine\Driver\Parser\Operator;


use PretendDb\Doctrine\Driver\Parser\Expression\ExpressionInterface;
use PretendDb\Doctrine\Driver\Parser\Expression\NotExpression;
use PretendDb\Doctrine\Driver\Parser\Token;

class LowPrecedenceNotOperator extends AbstractOperator
{
    /**
     * @return int
     */
    public function getPrecedence()
    {
        return 5;
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
        return $token->isLowPrecedenceNot();
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
