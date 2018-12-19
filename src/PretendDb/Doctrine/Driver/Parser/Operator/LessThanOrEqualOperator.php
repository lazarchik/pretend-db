<?php

namespace PretendDb\Doctrine\Driver\Parser\Operator;


use PretendDb\Doctrine\Driver\Parser\Expression\ComparisonExpression;
use PretendDb\Doctrine\Driver\Parser\Expression\ExpressionInterface;
use PretendDb\Doctrine\Driver\Parser\Token;

class LessThanOrEqualOperator extends AbstractOperator
{
    public function getPrecedence(): int
    {
        return 7;
    }

    public function isUnary(): bool
    {
        return false;
    }

    public function isBinary(): bool
    {
        return true;
    }

    public function isLeftAssociative(): bool
    {
        return true;
    }

    public function matchesToken(Token $token): bool
    {
        return $token->isLessThanOrEqual();
    }

    /**
     * @param ExpressionInterface[] $operands
     * @return ExpressionInterface
     */
    public function initAST(array $operands): ExpressionInterface
    {
        return new ComparisonExpression("<=", $operands[0], $operands[1]);
    }
}
