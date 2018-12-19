<?php

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

    public function addToken(Token $token): void
    {
        $this->tokens[] = $token;
    }

    /**
     * @return Token Current token if it's available. Otherwise an invalid token.
     */
    public function getCurrentToken(): Token
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
    public function getNextToken(): Token
    {
        $nextTokenIndex = $this->currentTokenIndex + 1;
        
        if (!array_key_exists($nextTokenIndex, $this->tokens)) {
            return Token::initInvalidToken();
        }
        
        return $this->tokens[$nextTokenIndex];
    }

    public function getCurrentTokenAndAdvanceCursor(): Token
    {
        $currentToken = $this->getCurrentToken();
        
        $this->advanceCursor();
        
        return $currentToken;
    }
    
    public function advanceCursor(): void
    {
        $this->currentTokenIndex++;
    }

    public function dump(): string
    {
        $tokenDumps = [];
        foreach ($this->tokens as $token) {
            $tokenDumps[] = $token->dump();
        }
        
        return "[".implode(", ", $tokenDumps)."]";
    }

    public function getCurrentTokenIndex(): int
    {
        return $this->currentTokenIndex;
    }

    public function getSourceText(int $startIndex, int $endIndex): string
    {
        $sourceText = "";
        foreach (array_slice($this->tokens, $startIndex, $endIndex - $startIndex) as $token) {
            $sourceText .= $token->getSourceString();
        }
        
        return $sourceText;
    }
}
