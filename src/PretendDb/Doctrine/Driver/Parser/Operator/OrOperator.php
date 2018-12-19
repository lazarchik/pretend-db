<?php

namespace PretendDb\Doctrine\Driver\Parser\Operator;


use PretendDb\Doctrine\Driver\Parser\Expression\ExpressionInterface;
use PretendDb\Doctrine\Driver\Parser\Expression\OrExpression;
use PretendDb\Doctrine\Driver\Parser\Token;

class OrOperator extends AbstractOperator
{
    public function getPrecedence(): int
    {
        return 2;
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
        return $token->isOr();
    }

    /**
     * @param ExpressionInterface[] $operands
     * @return ExpressionInterface
     */
    public function initAST(array $operands): ExpressionInterface
    {
        return new OrExpression($operands[0], $operands[1]);
    }
}
