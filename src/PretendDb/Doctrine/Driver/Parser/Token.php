<?php
/**
 * @author: Eugene Lazarchik
 * @date: 2/18/17
 */

namespace PretendDb\Doctrine\Driver\Parser;


class Token
{
    const TYPE_OPENING_PARENTHESIS                      = "OPENING_PARENTHESIS";
    const TYPE_CLOSING_PARENTHESIS                      = "CLOSING_PARENTHESIS";
    const TYPE_PLUS                                     = "PLUS";
    const TYPE_MINUS                                    = "MINUS";
    const TYPE_MULTIPLICATION                           = "MULTIPLICATION";
    const TYPE_DIVISION                                 = "DIVISION";
    const TYPE_EQUAL                                    = "EQUAL";
    const TYPE_NOT_EQUAL                                = "NOT_EQUAL";
    const TYPE_GREATER_THAN                             = "GREATER_THAN";
    const TYPE_GREATER_THAN_OR_EQUAL                    = "GREATER_THAN_OR_EQUAL";
    const TYPE_LESS_THAN                                = "LESS_THAN";
    const TYPE_LESS_THAN_OR_EQUAL                       = "LESS_THAN_OR_EQUAL";
    const TYPE_OR                                       = "OR";
    const TYPE_AND                                      = "AND";
    const TYPE_XOR                                      = "XOR";
    const TYPE_HIGH_PRECEDENCE_NOT                      = "HIGH_PRECENDENCE_NOT";
    const TYPE_LOW_PRECEDENCE_NOT                       = "LOW_PRECEDENCE_NOT";
    const TYPE_NUMBER_LITERAL                           = "NUMBER_LITERAL";
    const TYPE_STRING_LITERAL                           = "STRING_LITERAL";
    const TYPE_DATETIME_LITERAL                         = "DATETIME_LITERAL";
    const TYPE_HEXADECIMAL_LITERAL                      = "HEXADECIMAL_LITERAL";
    const TYPE_BIT_VALUE_LITERAL                        = "BIT_VALUE_LITERAL";
    const TYPE_BOOLEAN_LITERAL                          = "BOOLEAN_LITERAL";
    const TYPE_NULL_LITERAL                             = "NULL_LITERAL";
    const TYPE_SIMPLE_PLACEHOLDER                       = "SIMPLE_PLACEHOLDER";
    const TYPE_NAMED_PLACEHOLDER                        = "NAMED_PLACEHOLDER";
    const TYPE_PERIOD                                   = "PERIOD";
    const TYPE_COMMA                                    = "COMMA";
    const TYPE_WHITESPACE                               = "WHITESPACE";
    const TYPE_IDENTIFIER                               = "IDENTIFIER";
    
    /** @var int|null */
    protected $type;
    
    /** @var string */
    private $sourceString;

    /**
     * @param int $type
     * @param string $sourceString
     */
    protected function __construct($type, $sourceString)
    {
        $this->type = $type;
        $this->sourceString = $sourceString;
    }

    /**
     * @return string
     */
    public function getSourceString()
    {
        return $this->sourceString;
    }

    /**
     * @param string $sourceString
     * @return Token
     */
    public static function initStringLiteral($sourceString)
    {
        return new self(self::TYPE_STRING_LITERAL, $sourceString);
    }

    /**
     * @param string $sourceString
     * @return Token
     */
    public static function initIdentifier($sourceString)
    {
        return new self(self::TYPE_IDENTIFIER, $sourceString);
    }

    /**
     * @param string $sourceString
     * @return Token
     */
    public static function initNumberLiteral($sourceString)
    {
        return new self(self::TYPE_NUMBER_LITERAL, $sourceString);
    }

    /**
     * @param string $tokenSourceString
     * @return Token
     */
    public static function initHighPrecedenceNot($tokenSourceString)
    {
        return new self(self::TYPE_HIGH_PRECEDENCE_NOT, $tokenSourceString);
    }

    /**
     * @param string $sourceString
     * @return Token
     */
    public static function initOpeningParenthesis($sourceString)
    {
        return new self(self::TYPE_OPENING_PARENTHESIS, $sourceString);
    }

    /**
     * @param string $sourceString
     * @return Token
     */
    public static function initClosingParenthesis($sourceString)
    {
        return new self(self::TYPE_CLOSING_PARENTHESIS, $sourceString);
    }

    /**
     * @param string $sourceString
     * @return Token
     */
    public static function initPeriod($sourceString)
    {
        return new self(self::TYPE_PERIOD, $sourceString);
    }

    /**
     * @param string $sourceString
     * @return Token
     */
    public static function initEqual($sourceString)
    {
        return new self(self::TYPE_EQUAL, $sourceString);
    }

    /**
     * @param string $sourceString
     * @return Token
     */
    public static function initNotEqual($sourceString)
    {
        return new self(self::TYPE_NOT_EQUAL, $sourceString);
    }
    
    /**
     * @param string $sourceString
     * @return Token
     */
    public static function initGreaterThan($sourceString)
    {
        return new self(self::TYPE_GREATER_THAN, $sourceString);
    }
    
    /**
     * @param string $sourceString
     * @return Token
     */
    public static function initGreaterThanOrEqual($sourceString)
    {
        return new self(self::TYPE_GREATER_THAN_OR_EQUAL, $sourceString);
    }
    
    /**
     * @param string $sourceString
     * @return Token
     */
    public static function initLessThan($sourceString)
    {
        return new self(self::TYPE_LESS_THAN, $sourceString);
    }
    
    /**
     * @param string $sourceString
     * @return Token
     */
    public static function initLessThanOrEqual($sourceString)
    {
        return new self(self::TYPE_LESS_THAN_OR_EQUAL, $sourceString);
    }

    /**
     * @param string $sourceString
     * @return Token
     */
    public static function initSimplePlaceholder($sourceString)
    {
        return new self(self::TYPE_SIMPLE_PLACEHOLDER, $sourceString);
    }

    /**
     * @param string $sourceString
     * @return Token
     */
    public static function initComma($sourceString)
    {
        return new self(self::TYPE_COMMA, $sourceString);
    }

    /**
     * @param string $sourceString
     * @return Token
     */
    public static function initWhitespace($sourceString)
    {
        return new self(self::TYPE_WHITESPACE, $sourceString);
    }

    /**
     * @param string $sourceString
     * @return Token
     */
    public static function initAnd($sourceString)
    {
        return new self(self::TYPE_AND, $sourceString);
    }

    /**
     * @param string $sourceString
     * @return Token
     */
    public static function initOr($sourceString)
    {
        return new self(self::TYPE_OR, $sourceString);
    }

    /**
     * @param string $sourceString
     * @return Token
     */
    public static function initPlus($sourceString)
    {
        return new self(self::TYPE_PLUS, $sourceString);
    }

    /**
     * @param string $sourceString
     * @return Token
     */
    public static function initMinus($sourceString)
    {
        return new self(self::TYPE_MINUS, $sourceString);
    }

    /**
     * @param string $sourceString
     * @return Token
     */
    public static function initMultiplication($sourceString)
    {
        return new self(self::TYPE_MULTIPLICATION, $sourceString);
    }

    /**
     * @param string $sourceString
     * @return Token
     */
    public static function initDivision($sourceString)
    {
        return new self(self::TYPE_DIVISION, $sourceString);
    }

    /**
     * @param string $sourceString
     * @return Token
     */
    public static function initLowPrecedenceNot($sourceString)
    {
        return new self(self::TYPE_LOW_PRECEDENCE_NOT, $sourceString);
    }

    /**
     * @return Token
     */
    public static function initInvalidToken()
    {
        return new self(null, "");
    }
    
    /*
     * @return bool
     */
    public function isInvalidToken()
    {
        return null === $this->type;
    }

    /**
     * @return string
     */
    public function dump()
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
    
    /*
     * @return bool
     */
    public function isWhitespace()
    {
        return self::TYPE_WHITESPACE === $this->type;
    }
    
    /*
     * @return bool
     */
    public function isIdentifier()
    {
        return self::TYPE_IDENTIFIER === $this->type;
    }
    
    /*
     * @return bool
     */
    public function isPeriod()
    {
        return self::TYPE_PERIOD === $this->type;
    }
    
    /*
     * @return bool
     */
    public function isNumberLiteral()
    {
        return self::TYPE_NUMBER_LITERAL === $this->type;
    }
    
    /*
     * @return bool
     */
    public function isStringLiteral()
    {
        return self::TYPE_STRING_LITERAL === $this->type;
    }
    
    /*
     * @return bool
     */
    public function isEqual()
    {
        return self::TYPE_EQUAL === $this->type;
    }
    
    /*
     * @return bool
     */
    public function isNotEqual()
    {
        return self::TYPE_NOT_EQUAL === $this->type;
    }
    
    /*
     * @return bool
     */
    public function isGreaterThan()
    {
        return self::TYPE_GREATER_THAN === $this->type;
    }
    
    /*
     * @return bool
     */
    public function isGreaterThanOrEqual()
    {
        return self::TYPE_GREATER_THAN_OR_EQUAL === $this->type;
    }
    
    /*
     * @return bool
     */
    public function isLessThan()
    {
        return self::TYPE_LESS_THAN === $this->type;
    }
    
    /*
     * @return bool
     */
    public function isLessThanOrEqual()
    {
        return self::TYPE_LESS_THAN_OR_EQUAL === $this->type;
    }

    /**
     * @return bool
     */
    public function isComparisonOperator()
    {
        return $this->isEqual() || $this->isNotEqual()
            || $this->isGreaterThan() || $this->isGreaterThanOrEqual()
            || $this->isLessThan() || $this->isLessThanOrEqual();
    }

    /**
     * @return bool
     */
    public function isOpeningParenthesis()
    {
        return self::TYPE_OPENING_PARENTHESIS === $this->type;
    }

    /**
     * @return bool
     */
    public function isClosingParenthesis()
    {
        return self::TYPE_CLOSING_PARENTHESIS === $this->type;
    }

    /**
     * @return bool
     */
    public function isComma()
    {
        return self::TYPE_COMMA === $this->type;
    }

    /**
     * @return bool
     */
    public function isSimplePlaceholder()
    {
        return self::TYPE_SIMPLE_PLACEHOLDER === $this->type;
    }

    /**
     * @return bool
     */
    public function isNamedPlaceholder()
    {
        return self::TYPE_NAMED_PLACEHOLDER === $this->type;
    }

    /**
     * @return bool
     */
    public function isAnd()
    {
        return self::TYPE_AND === $this->type;
    }

    /**
     * @return bool
     */
    public function isOr()
    {
        return self::TYPE_OR === $this->type;
    }

    /**
     * @return bool
     */
    public function isHighPrecedenceNot()
    {
        return self::TYPE_HIGH_PRECEDENCE_NOT === $this->type;
    }

    /**
     * @return bool
     */
    public function isPlus()
    {
        return self::TYPE_PLUS === $this->type;
    }

    /**
     * @return bool
     */
    public function isMinus()
    {
        return self::TYPE_MINUS === $this->type;
    }

    /**
     * @return bool
     */
    public function isMultiplication()
    {
        return self::TYPE_MULTIPLICATION === $this->type;
    }

    /**
     * @return bool
     */
    public function isDivision()
    {
        return self::TYPE_DIVISION === $this->type;
    }

    /**
     * @return bool
     */
    public function isLowPrecedenceNot()
    {
        return self::TYPE_LOW_PRECEDENCE_NOT === $this->type;
    }
}
