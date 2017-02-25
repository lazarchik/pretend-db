<?php
/**
 * @author: Eugene Lazarchik
 * @date: 2/18/17
 */

namespace PretendDb\Doctrine\Driver\Parser;


use PretendDb\Doctrine\Driver\Parser\Expression\ExpressionInterface;
use PretendDb\Doctrine\Driver\Parser\Expression\FunctionCallExpression;
use PretendDb\Doctrine\Driver\Parser\Expression\NumberLiteralExpression;
use PretendDb\Doctrine\Driver\Parser\Expression\SimplePlaceholderExpression;
use PretendDb\Doctrine\Driver\Parser\Expression\TableFieldExpression;

/**
 * Parser for WHERE and SELECT expressions.
 * Precedence climbing algorithm described here:
 * @see http://www.engr.mun.ca/~theo/Misc/exp_parsing.htm#climbing
 * 
 * See phpdoc for class Grammar for the list of supported operators and precedence levels.
 */
class Parser
{
    /** @var Lexer */
    protected $lexer;
    
    /** @var Grammar */
    protected $grammar;

    /**
     * @param Lexer $lexer
     */
    public function __construct($lexer)
    {
        $this->lexer = $lexer;
        $this->grammar = new Grammar();
    }

    /**
     * @param TokenSequence $tokens
     * @param int $minPrecedence
     * @return ExpressionInterface
     * @throws \RuntimeException
     */
    protected function parseExpression($tokens, $minPrecedence)
    {
        $unaryOperator = $this->grammar->findUnaryOperatorFromToken($tokens->getCurrentToken());
        
        if ($unaryOperator) {
            
            $tokens->advanceCursor(); // Skip the unary operator token.
            
            $leftOperand = $unaryOperator->initAST([$this->parseExpression($tokens, $unaryOperator->getPrecedence())]);
        } else {
            
            $leftOperand = $this->parseSimpleExpressions($tokens);
        }
        
        $binaryOperator = $this->grammar->findBinaryOperatorFromToken($tokens->getCurrentToken());
        
        while ($binaryOperator && $binaryOperator->getPrecedence() >= $minPrecedence) {
            
            $tokens->advanceCursor(); // Skip the binary operator token.
            
            $operatorPrecedence = $binaryOperator->getPrecedence();
            
            $rightOperandMinPrecedence = $operatorPrecedence + ($binaryOperator->isLeftAssociative() ? 1 : 0);
            
            $rightOperand = $this->parseExpression($tokens, $rightOperandMinPrecedence);
            
            $leftOperand = $binaryOperator->initAST([$leftOperand, $rightOperand]);
            
            $binaryOperator = $this->grammar->findBinaryOperatorFromToken($tokens->getCurrentToken());
        }
        
        return $leftOperand;
    }

    /**
     * @param string $queryString
     * @return ExpressionInterface
     * @throws \RuntimeException
     */
    public function parse($queryString)
    {
        $queryTokens = $this->lexer->parse($queryString);
        
        $parsedExpression = $this->parseExpression($queryTokens, 0);
        
        if (!$queryTokens->getCurrentToken()->isInvalidToken()) {
            throw new \RuntimeException(
                "Invalid token after the end of the expression: ".$queryTokens->getCurrentToken()->dump()
            );
        }
        
        return $parsedExpression;
    }

    /**
     * @param TokenSequence $tokens
     * @return ExpressionInterface
     * @throws \RuntimeException
     */
    protected function parseSimpleExpressions($tokens)
    {
        $currentToken = $tokens->getCurrentToken();
        
        if ($currentToken->isIdentifier()) {
            
            if ($tokens->getNextToken()->isOpeningParenthesis()) {
                return $this->parseFunctionCallExpression($tokens);
            }
            
            return $this->parseTableField($tokens);
        }
        
        if ($currentToken->isNumberLiteral()) {
            return $this->parseNumberLiteral($tokens);
        }
        
        if ($currentToken->isSimplePlaceholder()) {
            $tokens->advanceCursor(); // Skip the simple placeholder token.
            
            return new SimplePlaceholderExpression();
        }
        
        if ($currentToken->isOpeningParenthesis()) {
            // Subqueries are currently not supported. We assume this is just the usual expression inside.
            
            $tokens->advanceCursor(); // Skip the opening parenthesis token
            
            $expressionInParentheses = $this->parseExpression($tokens, 0);
            
            $closingParenthesis = $tokens->getCurrentTokenAndAdvanceCursor();
            
            if (!$closingParenthesis->isClosingParenthesis()) {
                throw new \RuntimeException(
                    "Finished parsing a sub-expression inside parentheses but instead of the closing parenthesis got: "
                        .$closingParenthesis->dump()
                );
            }
            
            return $expressionInParentheses;
        }
        
        throw new \RuntimeException("Unknown token in simple expression: ".$tokens->getCurrentToken()->dump());
    }

    /**
     * @param TokenSequence $tokens
     * @return ExpressionInterface
     * @throws \RuntimeException
     */
    protected function parseFunctionCallExpression($tokens)
    {
        $functionNameToken = $tokens->getCurrentTokenAndAdvanceCursor();
        
        if (!$functionNameToken->isIdentifier()) {
            throw new \RuntimeException("First token of a function call expression must be an identifier. Got: "
                .$functionNameToken->dump());
        }
        
        $openingParenthesisToken = $tokens->getCurrentTokenAndAdvanceCursor();
        
        if (!$openingParenthesisToken->isOpeningParenthesis()) {
            throw new \RuntimeException(
                "Second token of a function call expression must be an opening parenthesis. Got: "
                    .$openingParenthesisToken->dump()
            );
        }
        
        if ($tokens->getCurrentToken()->isClosingParenthesis()) {
            return new FunctionCallExpression($functionNameToken->getSourceString(), []);
        }
        
        $functionArguments = [];
        
        $functionArguments[] = $this->parseExpression($tokens, 0);
        
        while ($tokens->getCurrentToken()->isComma()) {
            
            $tokens->advanceCursor(); // Skip the comma.
            
            $functionArguments[] = $this->parseExpression($tokens, 0);
        }
        
        $closingParenthesisToken = $tokens->getCurrentTokenAndAdvanceCursor();
        
        if (!$closingParenthesisToken->isClosingParenthesis()) {
            throw new \RuntimeException(
                "A comma or a closing parenthesis are expected after a function argument. Got: "
                    .$closingParenthesisToken->dump()
            );
        }
        
        return new FunctionCallExpression($functionNameToken->getSourceString(), $functionArguments);
    }

    /**
     * @param TokenSequence $tokens
     * @return NumberLiteralExpression
     * @throws \RuntimeException
     */
    protected function parseNumberLiteral($tokens)
    {
        $token = $tokens->getCurrentTokenAndAdvanceCursor();
        
        if (!$token->isNumberLiteral()) {
            throw new \RuntimeException("First token of a number literal expression must be a number literal. Got: "
                .$token->dump());
        }
        
        return new NumberLiteralExpression((float) $token->getSourceString());
    }

    /**
     * @param TokenSequence $tokens
     * @return TableFieldExpression
     * @throws \RuntimeException
     */
    protected function parseTableField($tokens)
    {
        $identifierToken1 = $tokens->getCurrentTokenAndAdvanceCursor();
        
        if (!$identifierToken1->isIdentifier()) {
            throw new \RuntimeException("First token of a table field expression must be an identifier. Got: "
                .$identifierToken1->dump());
        }
        
        if (!$tokens->getCurrentToken()->isPeriod()) {
            // No table or database name are present
            return new TableFieldExpression($identifierToken1->getSourceString(), null, null);
        }
        
        $tokens->advanceCursor(); // Skip the period token.
        
        $identifierToken2 = $tokens->getCurrentTokenAndAdvanceCursor();
        
        if (!$identifierToken2->isIdentifier()) {
            throw new \RuntimeException("Second token of a table field expression must be an identifier. Got: "
                .$identifierToken2->dump());
        }
        
        if (!$tokens->getCurrentToken()->isPeriod()) {
            // No database name is present
            return new TableFieldExpression(
                $identifierToken2->getSourceString(),
                $identifierToken1->getSourceString(),
                null
            );
        }
        
        $tokens->advanceCursor(); // Skip the period token.
        
        $identifierToken3 = $tokens->getCurrentTokenAndAdvanceCursor();
        
        if (!$identifierToken3->isIdentifier()) {
            throw new \RuntimeException("Third token of a table field expression must be an identifier. Got: "
                .$identifierToken3->dump());
        }
        
        return new TableFieldExpression(
            $identifierToken3->getSourceString(),
            $identifierToken2->getSourceString(),
            $identifierToken1->getSourceString()
        );
    }
}
