<?php
/**
 * @author: Eugene Lazarchik
 * @date: 2/18/17
 */

namespace PretendDb\Doctrine\Driver\Parser;


class Token
{
    const TYPE_OPENING_PARENTHESIS                      = "(";
    const TYPE_CLOSING_PARENTHESIS                      = ")";
    const TYPE_PLUS                                     = "+";
    const TYPE_MINUS                                    = "-";
    const TYPE_MULTIPLICATION                           = "*";
    const TYPE_DIVISION                                 = "/";
    const TYPE_EQUAL                                    = "=";
    const TYPE_NOT_EQUAL                                = "!=";
    const TYPE_GREATER_THAN                             = ">";
    const TYPE_GREATER_THAN_OR_EQUAL                    = ">=";
    const TYPE_LESS_THAN                                = "<";
    const TYPE_LESS_THAN_OR_EQUAL                       = "<=";
    const TYPE_OR                                       = "||";
    const TYPE_AND                                      = "&&";
    const TYPE_XOR                                      = "XOR";
    const TYPE_NOT                                      = "!";
    const TYPE_NUMBER_LITERAL                           = "number";
    const TYPE_STRING_LITERAL                           = "string";
    const TYPE_DATETIME_LITERAL                         = "datetime";
    const TYPE_HEXADECIMAL_LITERAL                      = "hexadecimal";
    const TYPE_BIT_VALUE_LITERAL                        = "bit_value";
    const TYPE_BOOLEAN_LITERAL                          = "boolean";
    const TYPE_NULL_LITERAL                             = "null";
    const TYPE_SIMPLE_PLACEHOLDER                       = "?";
    const TYPE_NAMED_PLACEHOLDER                        = "named_placeholder";
    const TYPE_PERIOD                                   = ".";
    const TYPE_COMMA                                    = ",";
    const TYPE_WHITESPACE                               = "whitespace";
    const TYPE_IDENTIFIER                               = "identifier";
    
    /** @var int */
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
        return new self(self::TYPE_PERIOD, $sourceString);
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
    public static function initLessThan($sourceString)
    {
        return new self(self::TYPE_LESS_THAN, $sourceString);
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
    
    /*
     * @return bool
     */
    public function isWhitespace()
    {
        return self::TYPE_WHITESPACE == $this->type;
    }
}
