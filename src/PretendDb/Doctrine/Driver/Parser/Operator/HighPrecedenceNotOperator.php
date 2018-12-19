<?php

namespace PretendDb\Doctrine\Driver\Parser\Operator;


use PretendDb\Doctrine\Driver\Parser\Expression\ExpressionInterface;
use PretendDb\Doctrine\Driver\Parser\Expression\NotExpression;
use PretendDb\Doctrine\Driver\Parser\Token;

class HighPrecedenceNotOperator extends AbstractOperator
{
    public function getPrecedence(): int
    {
        return 15;
    }

    public function isUnary(): bool
    {
        return true;
    }

    public function isBinary(): bool
    {
        return false;
    }

    public function isLeftAssociative(): bool
    {
        return false;
    }

    public function matchesToken(Token $token): bool
    {
        return $token->isHighPrecedenceNot();
    }

    /**
     * @param string $sourceString
     * @param ExpressionInterface[] $operands
     * @return ExpressionInterface
     */
    public function initAST(string $sourceString, array $operands): ExpressionInterface
    {
        return new NotExpression($sourceString, $operands[0]);
    }
}
