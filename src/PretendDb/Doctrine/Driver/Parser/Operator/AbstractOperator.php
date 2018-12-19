<?php

namespace PretendDb\Doctrine\Driver\Parser\Operator;


use PretendDb\Doctrine\Driver\Parser\Expression\ExpressionInterface;
use PretendDb\Doctrine\Driver\Parser\Token;

abstract class AbstractOperator
{
    abstract public function getPrecedence(): int;
    abstract public function isUnary(): bool;
    abstract public function isBinary(): bool;
    abstract public function isLeftAssociative(): bool;
    abstract public function matchesToken(Token $token): bool;

    /**
     * @param ExpressionInterface[] $operands
     * @return ExpressionInterface
     */
    abstract public function initAST(array $operands): ExpressionInterface;
}
