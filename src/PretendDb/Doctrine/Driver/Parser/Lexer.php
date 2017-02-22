<?php
/**
 * @author: Eugene Lazarchik
 * @date: 2/18/17
 */

namespace PretendDb\Doctrine\Driver\Parser;


class Lexer
{
    /**
     * @param string $queryString
     * @param string $tokenRegexString
     * @return string|null
     */
    protected function checkTokenRegex($queryString, $tokenRegexString)
    {
        if (!preg_match("~^(".$tokenRegexString.")~i", $queryString, $tokenSourceStringMatches)) {
            return null;
        }
        
        return $tokenSourceStringMatches[1];
    }
    
    /**
     * @param string $queryString
     * @param string $tokenString
     * @return string|null
     */
    protected function checkTokenString($queryString, $tokenString)
    {
        if (0 !== stripos($queryString, $tokenString)) {
            return null;
        }
        
        // The case of the characters may differ from $tokenString.
        return substr($queryString, 0, strlen($tokenString)); 
    }

    /**
     * @param string $queryString
     * @return TokenSequence
     * @throws \RuntimeException
     */
    public function parse($queryString)
    {
        $queryString = (string)$queryString;
        
        $tokens = new TokenSequence();
        
        while (strlen($queryString) > 0) {
            
            $nextToken = $this->parseNextToken($queryString);
            
            // No need to store whitespaces in the token sequence.
            if (!$nextToken->isWhitespace()) {
                $tokens->addToken($nextToken);
            }
            
            if (strlen($nextToken->getSourceString()) <= 0) {
                throw new \RuntimeException(
                    "Token size of zero length or smaller. This should never happen. Infinite loop failsafe triggered");
            }
            
            // Now remove the token from the query string, so that we can advance to parsing the next token
            $queryString = substr($queryString, strlen($nextToken->getSourceString()));
        }
        
        return $tokens;
    }

    /**
     * @param string $queryString
     * @return Token
     * @throws \RuntimeException
     */
    protected function parseNextToken($queryString)
    {
        if (null !== ($tokenSourceString = $this->checkTokenRegex($queryString, "[ \t\r\n]+"))) {
            return Token::initWhitespace($tokenSourceString);
        }
        
        if (null !== ($tokenSourceString = $this->checkTokenString($queryString, "("))) {
            return Token::initOpeningParenthesis($tokenSourceString);
        }
        
        if (null !== ($tokenSourceString = $this->checkTokenString($queryString, ")"))) {
            return Token::initClosingParenthesis($tokenSourceString);
        }
        
        if (null !== ($tokenSourceString = $this->checkTokenRegex($queryString, "[-+]?\.[0-9]+(?:e[+-]?[0-9]+)?"))) {
            return Token::initNumberLiteral($tokenSourceString);
        }
        
        if (null !== ($tokenSourceString = $this->checkTokenString($queryString, "."))) {
            return Token::initPeriod($tokenSourceString);
        }
        
        if (null !== ($tokenSourceString = $this->checkTokenString($queryString, "="))) {
            return Token::initEqual($tokenSourceString);
        }
        
        if (null !== ($tokenSourceString = $this->checkTokenRegex($queryString, "!=|<>"))) {
            return Token::initNotEqual($tokenSourceString);
        }
        
        if (null !== ($tokenSourceString = $this->checkTokenString($queryString, ">"))) {
            return Token::initGreaterThan($tokenSourceString);
        }
        
        if (null !== ($tokenSourceString = $this->checkTokenString($queryString, ">="))) {
            return Token::initGreaterThanOrEqual($tokenSourceString);
        }
        
        if (null !== ($tokenSourceString = $this->checkTokenString($queryString, "<"))) {
            return Token::initLessThan($tokenSourceString);
        }
        
        if (null !== ($tokenSourceString = $this->checkTokenString($queryString, "<="))) {
            return Token::initLessThanOrEqual($tokenSourceString);
        }
        if (null !== ($tokenSourceString = $this->checkTokenString($queryString, "+"))) {
            return Token::initPlus($tokenSourceString);
        }
        
        if (null !== ($tokenSourceString = $this->checkTokenString($queryString, "-"))) {
            return Token::initMinus($tokenSourceString);
        }
        
        if (null !== ($tokenSourceString = $this->checkTokenString($queryString, "*"))) {
            return Token::initMultiplication($tokenSourceString);
        }
        
        if (null !== ($tokenSourceString = $this->checkTokenString($queryString, "/"))) {
            return Token::initDivision($tokenSourceString);
        }
        
        if (null !== ($tokenSourceString = $this->checkTokenString($queryString, "?"))) {
            return Token::initSimplePlaceholder($tokenSourceString);
        }
        
        if (null !== ($tokenSourceString = $this->checkTokenString($queryString, ","))) {
            return Token::initComma($tokenSourceString);
        }
        
        if (null !== ($tokenSourceString = $this->checkTokenRegex($queryString, "AND|&&"))) {
            return Token::initAnd($tokenSourceString);
        }
        
        if (null !== ($tokenSourceString = $this->checkTokenString($queryString, "NOT"))) {
            return Token::initLowPrecedenceNot($tokenSourceString);
        }
        
        if (null !== ($tokenSourceString = $this->checkTokenString($queryString, "!"))) {
            return Token::initHighPrecedenceNot($tokenSourceString);
        }
        
        if (null !== ($tokenSourceString = $this->checkTokenRegex($queryString, "OR|\\|\\|"))) {
            return Token::initOr($tokenSourceString);
        }
        
        if (null !== ($tokenSourceString = $this->checkTokenRegex($queryString, "[a-z\$_][a-z0-9\$_]+"))) {
            return Token::initIdentifier($tokenSourceString);
        }
        
        if (null !== (
            $tokenSourceString = $this->checkTokenRegex($queryString, "[-+]?[0-9]+(?:\.[0-9]*)?(?:e[+-]?[0-9]+)?"))
        ) {
            return Token::initNumberLiteral($tokenSourceString);
        }
        
        throw new \RuntimeException("Can't parse any known literals: '".$queryString."'");
    }
}
