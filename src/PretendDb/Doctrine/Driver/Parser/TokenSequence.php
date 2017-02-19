<?php
/**
 * @author: Eugene Lazarchik
 * @date: 2/18/17
 */

namespace PretendDb\Doctrine\Driver\Parser;

/**
 * Represents a list of tokens that can be traversed using a cursor
 */
class TokenSequence
{
    /** @var Token[] */
    protected $tokens = [];
    
    /** @var int */
    protected $currentTokenIndex = 0;

    /**
     * @param Token $token
     */
    public function addToken(Token $token)
    {
        $this->tokens[] = $token;
    }

    /**
     * @return Token Current token if it's available. Otherwise an invalid token.
     */
    public function getCurrentToken()
    {
        if (!array_key_exists($this->currentTokenIndex, $this->tokens)) {
            return Token::initInvalidToken();
        }
        
        return $this->tokens[$this->currentTokenIndex];
    }

    /**
     * Looks ahead without advancing the cursor
     * @return Token Next token if it's available. Otherwise an invalid token.
     */
    public function getNextToken()
    {
        $nextTokenIndex = $this->currentTokenIndex + 1;
        
        if (!array_key_exists($nextTokenIndex, $this->tokens)) {
            return Token::initInvalidToken();
        }
        
        return $this->tokens[$nextTokenIndex];
    }

    /**
     * @return Token
     */
    public function getCurrentTokenAndAdvanceCursor()
    {
        $currentToken = $this->getCurrentToken();
        
        $this->advanceCursor();
        
        return $currentToken;
    }
    
    public function advanceCursor()
    {
        $this->currentTokenIndex++;;
    }
}
