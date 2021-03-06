<?php

namespace PretendDb\Doctrine\Driver\Parser\Operator;

use PretendDb\Doctrine\Driver\Parser\Expression\ExpressionInterface;
use PretendDb\Doctrine\Driver\Parser\Expression\InExpression;
use PretendDb\Doctrine\Driver\Parser\Token;

class InOperator extends AbstractOperator
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
        return $token->isIn();
    }

    /**
     * @param string $sourceString
     * @param ExpressionInterface[] $operands
     * @return ExpressionInterface
     */
    public function initAST(string $sourceString, array $operands): ExpressionInterface
    {
        $expressionsOnTheRight = array_slice($operands, 1);
        
        return new InExpression($sourceString, $operands[0], $expressionsOnTheRight);
    }
}
