<?php
/**
 * @author: Eugene Lazarchik
 * @date: 2/21/17
 */

namespace PretendDb\Doctrine\Driver\Parser\Operator;


use PretendDb\Doctrine\Driver\Parser\Expression\ExpressionInterface;
use PretendDb\Doctrine\Driver\Parser\Expression\OrExpression;
use PretendDb\Doctrine\Driver\Parser\Token;

class OrOperator extends AbstractOperator
{
    /**
     * @return int
     */
    public function getPrecedence()
    {
        return 2;
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
        return $token->isOr();
    }

    /**
     * @param ExpressionInterface[] $operands
     * @return ExpressionInterface
     */
    public function initAST($operands)
    {
        return new OrExpression($operands[0], $operands[1]);
    }
}
