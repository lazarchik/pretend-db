<?php
/**
 * @author: Eugene Lazarchik
 * @date: 2/21/17
 */

namespace PretendDb\Doctrine\Driver\Parser\Operator;


use PretendDb\Doctrine\Driver\Parser\Expression\AndExpression;
use PretendDb\Doctrine\Driver\Parser\Expression\ExpressionInterface;
use PretendDb\Doctrine\Driver\Parser\Token;

class AndOperator extends AbstractOperator
{
    /**
     * @return int
     */
    public function getPrecedence()
    {
        return 4;
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
        return $token->isAnd();
    }

    /**
     * @param ExpressionInterface[] $operands
     * @return ExpressionInterface
     */
    public function initAST($operands)
    {
        return new AndExpression($operands[0], $operands[1]);
    }
}
