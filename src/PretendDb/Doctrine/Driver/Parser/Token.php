<?php

namespace PretendDb\Doctrine\Driver\Parser;


class Token
{
    protected const TYPE_OPENING_PARENTHESIS                      = "OPENING_PARENTHESIS";
    protected const TYPE_CLOSING_PARENTHESIS                      = "CLOSING_PARENTHESIS";
    protected const TYPE_PLUS                                     = "PLUS";
    protected const TYPE_MINUS                                    = "MINUS";
    protected const TYPE_MULTIPLICATION                           = "MULTIPLICATION";
    protected const TYPE_DIVISION                                 = "DIVISION";
    protected const TYPE_EQUAL                                    = "EQUAL";
    protected const TYPE_NOT_EQUAL                                = "NOT_EQUAL";
    protected const TYPE_GREATER_THAN                             = "GREATER_THAN";
    protected const TYPE_GREATER_THAN_OR_EQUAL                    = "GREATER_THAN_OR_EQUAL";
    protected const TYPE_LESS_THAN                                = "LESS_THAN";
    protected const TYPE_LESS_THAN_OR_EQUAL                       = "LESS_THAN_OR_EQUAL";
    protected const TYPE_OR                                       = "OR";
    protected const TYPE_AND                                      = "AND";
    protected const TYPE_XOR                                      = "XOR";
    protected const TYPE_HIGH_PRECEDENCE_NOT                      = "HIGH_PRECENDENCE_NOT";
    protected const TYPE_LOW_PRECEDENCE_NOT                       = "LOW_PRECEDENCE_NOT";
    protected const TYPE_NUMBER_LITERAL                           = "NUMBER_LITERAL";
    protected const TYPE_STRING_LITERAL                           = "STRING_LITERAL";
    protected const TYPE_DATETIME_LITERAL                         = "DATETIME_LITERAL";
    protected const TYPE_HEXADECIMAL_LITERAL                      = "HEXADECIMAL_LITERAL";
    protected const TYPE_BIT_VALUE_LITERAL                        = "BIT_VALUE_LITERAL";
    protected const TYPE_BOOLEAN_LITERAL                          = "BOOLEAN_LITERAL";
    protected const TYPE_NULL_LITERAL                             = "NULL_LITERAL";
    protected const TYPE_SIMPLE_PLACEHOLDER                       = "SIMPLE_PLACEHOLDER";
    protected const TYPE_NAMED_PLACEHOLDER                        = "NAMED_PLACEHOLDER";
    protected const TYPE_PERIOD                                   = "PERIOD";
    protected const TYPE_COMMA                                    = "COMMA";
    protected const TYPE_WHITESPACE                               = "WHITESPACE";
    protected const TYPE_IDENTIFIER                               = "IDENTIFIER";
    protected const TYPE_IN                                       = "IN";
    protected const TYPE_SELECT                                   = "SELECT";
    
    /** @var int|null */
    protected $type;
    
    /** @var string */
    private $sourceString;

    protected function __construct(int $type, string $sourceString)
    {
        $this->type = $type;
        $this->sourceString = $sourceString;
    }

    public function getSourceString(): string
    {
        return $this->sourceString;
    }

    public static function initStringLiteral(string $sourceString): Token
    {
        return new self(self::TYPE_STRING_LITERAL, $sourceString);
    }
    
    public static function initIdentifier(string $sourceString): Token
    {
        return new self(self::TYPE_IDENTIFIER, $sourceString);
    }
    
    public static function initNumberLiteral(string $sourceString): Token
    {
        return new self(self::TYPE_NUMBER_LITERAL, $sourceString);
    }

    /**
     * @param string $tokenSourceString
     * @return Token
     */
    public static function initHighPrecedenceNot(string $tokenSourceString): Token
    {
        return new self(self::TYPE_HIGH_PRECEDENCE_NOT, $tokenSourceString);
    }
    
    public static function initOpeningParenthesis(string $sourceString): Token
    {
        return new self(self::TYPE_OPENING_PARENTHESIS, $sourceString);
    }
    
    public static function initClosingParenthesis(string $sourceString): Token
    {
        return new self(self::TYPE_CLOSING_PARENTHESIS, $sourceString);
    }
    
    public static function initPeriod(string $sourceString): Token
    {
        return new self(self::TYPE_PERIOD, $sourceString);
    }
    
    public static function initEqual(string $sourceString): Token
    {
        return new self(self::TYPE_EQUAL, $sourceString);
    }
    
    public static function initNotEqual(string $sourceString): Token
    {
        return new self(self::TYPE_NOT_EQUAL, $sourceString);
    }
    
    public static function initGreaterThan(string $sourceString): Token
    {
        return new self(self::TYPE_GREATER_THAN, $sourceString);
    }
    
    public static function initGreaterThanOrEqual(string $sourceString): Token
    {
        return new self(self::TYPE_GREATER_THAN_OR_EQUAL, $sourceString);
    }
    
    public static function initLessThan(string $sourceString): Token
    {
        return new self(self::TYPE_LESS_THAN, $sourceString);
    }
    
    public static function initLessThanOrEqual(string $sourceString): Token
    {
        return new self(self::TYPE_LESS_THAN_OR_EQUAL, $sourceString);
    }
    
    public static function initSimplePlaceholder(string $sourceString): Token
    {
        return new self(self::TYPE_SIMPLE_PLACEHOLDER, $sourceString);
    }
    
    public static function initComma(string $sourceString): Token
    {
        return new self(self::TYPE_COMMA, $sourceString);
    }

    public static function initWhitespace(string $sourceString): Token
    {
        return new self(self::TYPE_WHITESPACE, $sourceString);
    }

    public static function initAnd(string $sourceString): Token
    {
        return new self(self::TYPE_AND, $sourceString);
    }

    public static function initOr(string $sourceString): Token
    {
        return new self(self::TYPE_OR, $sourceString);
    }

    public static function initPlus(string $sourceString): Token
    {
        return new self(self::TYPE_PLUS, $sourceString);
    }

    public static function initMinus(string $sourceString): Token
    {
        return new self(self::TYPE_MINUS, $sourceString);
    }

    public static function initMultiplication(string $sourceString): Token
    {
        return new self(self::TYPE_MULTIPLICATION, $sourceString);
    }

    public static function initDivision(string $sourceString): Token
    {
        return new self(self::TYPE_DIVISION, $sourceString);
    }

    public static function initLowPrecedenceNot(string $sourceString): Token
    {
        return new self(self::TYPE_LOW_PRECEDENCE_NOT, $sourceString);
    }

    public static function initIn(string $sourceString): Token
    {
        return new self(self::TYPE_IN, $sourceString);
    }

    public static function initSelect(string $sourceString): Token
    {
        return new self(self::TYPE_SELECT, $sourceString);
    }

    public static function initInvalidToken(): Token
    {
        return new self(null, "");
    }
    
    public function isInvalidToken(): bool
    {
        return null === $this->type;
    }

    public function dump(): string
    {
        if ($this->isInvalidToken()) {
            return "INVALID_TOKEN";
        }
        
        $dumpString = (string)$this->type;
        
        if (!in_array($this->type, [])) {
            return $dumpString."(".$this->getSourceString().")";
        }
        
        return $dumpString;
    }
    
    public function isWhitespace(): bool
    {
        return self::TYPE_WHITESPACE === $this->type;
    }
    
    public function isIdentifier(): bool
    {
        return self::TYPE_IDENTIFIER === $this->type;
    }
    
    public function isPeriod(): bool
    {
        return self::TYPE_PERIOD === $this->type;
    }
    
    public function isNumberLiteral(): bool
    {
        return self::TYPE_NUMBER_LITERAL === $this->type;
    }
    
    public function isStringLiteral(): bool
    {
        return self::TYPE_STRING_LITERAL === $this->type;
    }
    
    public function isEqual(): bool
    {
        return self::TYPE_EQUAL === $this->type;
    }
    
    public function isNotEqual(): bool
    {
        return self::TYPE_NOT_EQUAL === $this->type;
    }
    
    public function isGreaterThan(): bool
    {
        return self::TYPE_GREATER_THAN === $this->type;
    }
    
    public function isGreaterThanOrEqual(): bool
    {
        return self::TYPE_GREATER_THAN_OR_EQUAL === $this->type;
    }
    
    public function isLessThan(): bool
    {
        return self::TYPE_LESS_THAN === $this->type;
    }
    
    public function isLessThanOrEqual(): bool
    {
        return self::TYPE_LESS_THAN_OR_EQUAL === $this->type;
    }
    
    public function isComparisonOperator(): bool
    {
        return $this->isEqual() || $this->isNotEqual()
            || $this->isGreaterThan() || $this->isGreaterThanOrEqual()
            || $this->isLessThan() || $this->isLessThanOrEqual();
    }
    
    public function isOpeningParenthesis(): bool
    {
        return self::TYPE_OPENING_PARENTHESIS === $this->type;
    }
    
    public function isClosingParenthesis(): bool
    {
        return self::TYPE_CLOSING_PARENTHESIS === $this->type;
    }
    
    public function isComma(): bool
    {
        return self::TYPE_COMMA === $this->type;
    }
    
    public function isSimplePlaceholder(): bool
    {
        return self::TYPE_SIMPLE_PLACEHOLDER === $this->type;
    }
    
    public function isNamedPlaceholder(): bool
    {
        return self::TYPE_NAMED_PLACEHOLDER === $this->type;
    }
    
    public function isAnd(): bool
    {
        return self::TYPE_AND === $this->type;
    }
    
    public function isOr(): bool
    {
        return self::TYPE_OR === $this->type;
    }
    
    public function isHighPrecedenceNot(): bool
    {
        return self::TYPE_HIGH_PRECEDENCE_NOT === $this->type;
    }
    
    public function isPlus(): bool
    {
        return self::TYPE_PLUS === $this->type;
    }
    
    public function isMinus(): bool
    {
        return self::TYPE_MINUS === $this->type;
    }
    
    public function isMultiplication(): bool
    {
        return self::TYPE_MULTIPLICATION === $this->type;
    }
    
    public function isDivision(): bool
    {
        return self::TYPE_DIVISION === $this->type;
    }
    
    public function isLowPrecedenceNot(): bool
    {
        return self::TYPE_LOW_PRECEDENCE_NOT === $this->type;
    }
    
    public function isIn(): bool
    {
        return self::TYPE_IN === $this->type;
    }
    
    public function isSelect(): bool
    {
        return self::TYPE_SELECT === $this->type;
    }
}
