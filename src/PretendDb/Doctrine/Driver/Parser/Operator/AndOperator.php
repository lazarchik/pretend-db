<?php

namespace PretendDb\Doctrine\Driver\Parser\Operator;


use PretendDb\Doctrine\Driver\Parser\Expression\AndExpression;
use PretendDb\Doctrine\Driver\Parser\Expression\ExpressionInterface;
use PretendDb\Doctrine\Driver\Parser\Token;

class AndOperator extends AbstractOperator
{
    public function getPrecedence(): int
    {
        return 4;
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
        return $token->isAnd();
    }

    /**
     * @param string $sourceString
     * @param ExpressionInterface[] $operands
     * @return ExpressionInterface
     */
    public function initAST(string $sourceString, array $operands): ExpressionInterface
    {
        return new AndExpression($sourceString, $operands[0], $operands[1]);
    }
}
