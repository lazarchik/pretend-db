<?php

namespace PretendDb\Doctrine\Driver\Parser;


use PretendDb\Doctrine\Driver\Parser\Operator\AbstractOperator;
use PretendDb\Doctrine\Driver\Parser\Operator\AndOperator;
use PretendDb\Doctrine\Driver\Parser\Operator\DivisionOperator;
use PretendDb\Doctrine\Driver\Parser\Operator\EqualOperator;
use PretendDb\Doctrine\Driver\Parser\Operator\GreaterThanOperator;
use PretendDb\Doctrine\Driver\Parser\Operator\GreaterThanOrEqualOperator;
use PretendDb\Doctrine\Driver\Parser\Operator\HighPrecedenceNotOperator;
use PretendDb\Doctrine\Driver\Parser\Operator\InOperator;
use PretendDb\Doctrine\Driver\Parser\Operator\LessThanOperator;
use PretendDb\Doctrine\Driver\Parser\Operator\LessThanOrEqualOperator;
use PretendDb\Doctrine\Driver\Parser\Operator\LowPrecedenceNotOperator;
use PretendDb\Doctrine\Driver\Parser\Operator\MinusOperator;
use PretendDb\Doctrine\Driver\Parser\Operator\MultiplicationOperator;
use PretendDb\Doctrine\Driver\Parser\Operator\NotEqualOperator;
use PretendDb\Doctrine\Driver\Parser\Operator\OrOperator;
use PretendDb\Doctrine\Driver\Parser\Operator\PlusOperator;

/**
 * @see http://www.engr.mun.ca/~theo/Misc/exp_parsing.htm#climbing
 * 
 * Precedences:
 * * P17. INTERVAL
 * * P16. BINARY, COLLATE
 * * P15. !
 * * P14. - (unary minus), ~ (unary bit inversion)
 * * P13. ^
 * * P12. *, /, DIV, %, MOD
 * * P11. -, +
 * * P10. <<, >>
 * * P9. &
 * * P8. |
 * * P7. = (comparison), <=>, >=, >, <=, <, <>, !=, IS, LIKE, REGEXP, IN
 * * P6. BETWEEN, CASE, WHEN, THEN, ELSE
 * * P5. NOT
 * * P4. AND, &&
 * * P3. XOR
 * * P2. OR, ||
 * * P1. = (assignment), :=
 */
class Grammar
{
    /**
     * @return AbstractOperator[]
     */
    public function getOperators(): array
    {
        return [
            new AndOperator(),
            new OrOperator(),
            new LowPrecedenceNotOperator(),
            new HighPrecedenceNotOperator(),
            new PlusOperator(),
            new MinusOperator(),
            new MultiplicationOperator(),
            new DivisionOperator(),
            new EqualOperator(),
            new NotEqualOperator(),
            new GreaterThanOperator(),
            new GreaterThanOrEqualOperator(),
            new LessThanOperator(),
            new LessThanOrEqualOperator(),
            new InOperator(),
        ];
    }

    public function findBinaryOperatorFromToken(Token $token): ?AbstractOperator
    {
        foreach ($this->getOperators() as $operatorObject) {
            if ($operatorObject->matchesToken($token) && $operatorObject->isBinary()) {
                return $operatorObject;
            }
        }
        
        return null;
    }

    public function findUnaryOperatorFromToken(Token $token): ?AbstractOperator
    {
        foreach ($this->getOperators() as $operatorObject) {
            if ($operatorObject->matchesToken($token) && $operatorObject->isUnary()) {
                return $operatorObject;
            }
        }
        
        return null;
    }
}
