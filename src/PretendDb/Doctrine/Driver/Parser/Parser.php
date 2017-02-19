<?php
/**
 * @author: Eugene Lazarchik
 * @date: 2/18/17
 */

namespace PretendDb\Doctrine\Driver\Parser;


use PretendDb\Doctrine\Driver\Parser\Expression\AndExpression;
use PretendDb\Doctrine\Driver\Parser\Expression\ComparisonExpression;
use PretendDb\Doctrine\Driver\Parser\Expression\ExpressionInterface;
use PretendDb\Doctrine\Driver\Parser\Expression\FunctionCallExpression;
use PretendDb\Doctrine\Driver\Parser\Expression\NotExpression;
use PretendDb\Doctrine\Driver\Parser\Expression\NumberLiteralExpression;
use PretendDb\Doctrine\Driver\Parser\Expression\OrExpression;
use PretendDb\Doctrine\Driver\Parser\Expression\SimplePlaceholderExpression;
use PretendDb\Doctrine\Driver\Parser\Expression\TableFieldExpression;

class Parser
{
    /** @var Lexer */
    private $lexer;

    /**
     * @param Lexer $lexer
     */
    public function __construct($lexer)
    {
        $this->lexer = $lexer;
    }

    /**
     * @param string $queryString
     * @throws \RuntimeException
     */
    public function parse($queryString)
    {
        $queryTokens = $this->lexer->parse($queryString);
        
        $parsedExpression = $this->parseArbitraryExpression($queryTokens);
        
        echo "parsedExpression:\n";
        echo $parsedExpression->dump()."\n";
    }

    /**
     * @param TokenSequence $tokens
     * @return ExpressionInterface
     * @throws \RuntimeException
     */
    protected function parseArbitraryExpression($tokens)
    {
        return $this->parseOrExpressions($tokens);
    }

    /**
     * Parse an expression that can have operators of any precedence up to OR
     * @param TokenSequence $tokens
     * @return ExpressionInterface
     * @throws \RuntimeException
     */
    protected function parseOrExpressions($tokens)
    {
        $currentExpression = $this->parseAndExpressions($tokens);
        
        while ($tokens->getCurrentToken()->isOr()) {

            $tokens->advanceCursor(); // Skip the "or" token.

            $rightOperand = $this->parseAndExpressions($tokens);

            // Combine left and right operand and assign to the left operand, in case we got more ORs on the right
            $currentExpression = new OrExpression($currentExpression, $rightOperand);
        }
        
        return $currentExpression;
    }

    /**
     * Parse an expression that can have operators of any precedence up to AND (so no ORs)
     * @param TokenSequence $tokens
     * @return ExpressionInterface
     * @throws \RuntimeException
     */
    protected function parseAndExpressions($tokens)
    {
        $leftOperand = $this->parseNotExpressions($tokens);
        
        if (!$tokens->getCurrentToken()->isAnd()) {
            return $leftOperand;
        }
        
        $tokens->advanceCursor(); // Skip the "and" token.
        
        $rightOperand = $this->parseNotExpressions($tokens);
        
        return new AndExpression($leftOperand, $rightOperand);
    }

    /**
     * Parse an expression that can have operators of any precedence up to NOT (so no ORs or ANDs)
     * @param TokenSequence $tokens
     * @return ExpressionInterface
     * @throws \RuntimeException
     */
    protected function parseNotExpressions($tokens)
    {
        if (!$tokens->getCurrentToken()->isNot()) {
            return $this->parseComparisonExpressions($tokens);
        }
        
        $tokens->advanceCursor(); // Skip the "not" token.
        
        if (!$tokens->getCurrentToken()->isNot()) {
            // We know that operand doesn't start with NOT, 
            return new NotExpression($this->parseComparisonExpressions($tokens));
        }
        
        // Another NOT detected. Parse another "NOT expression".
        return new NotExpression($this->parseNotExpressions($tokens));
    }

    /**
     * Parse an expression with operators of any precedence up to comparison operators (no ORs, ANDs or NOTs).
     * Well, NOTs are actually allowed as right operands of comparison operators, but not as left ones.
     * @param TokenSequence $tokens
     * @return ExpressionInterface
     * @throws \RuntimeException
     */
    protected function parseComparisonExpressions($tokens)
    {
        $leftOperand = $this->parseSimpleExpressions($tokens);
        
        $currentToken = $tokens->getCurrentToken();
            
        if (!$currentToken->isComparisonOperator()) {
            return $leftOperand;
        }
        
        $operatorTypeToken = $tokens->getCurrentTokenAndAdvanceCursor();
        
        $rightOperand = $this->parseNotExpressions($tokens);
        
        return new ComparisonExpression($operatorTypeToken->getSourceString(), $leftOperand, $rightOperand);
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
            
            $tableFieldExpression = $this->parseTableField($tokens);
            
            return $tableFieldExpression;
        }
        
        if ($currentToken->isNumberLiteral()) {
            $numberLiteralExpression = $this->parseNumberLiteral($tokens);
            
            return $numberLiteralExpression;
        }
        
        if ($currentToken->isSimplePlaceholder()) {
            $tokens->advanceCursor(); // Skip the simple placeholder token.
            
            return new SimplePlaceholderExpression();
        }
        
        if ($currentToken->isOpeningParenthesis()) {
            // Subqueries are currently not supported. We assume this is just the usual expression inside.
            
            $tokens->advanceCursor(); // Skip the opening parenthesis token
            
            $expressionInParentheses = $this->parseArbitraryExpression($tokens);
            
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
        
        $functionArguments[] = $this->parseArbitraryExpression($tokens);
        
        while ($tokens->getCurrentToken()->isComma()) {
            
            $tokens->advanceCursor(); // Skip the comma.
            
            $functionArguments[] = $this->parseArbitraryExpression($tokens);
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
                $identifierToken1->getSourceString(),
                $identifierToken2->getSourceString(),
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
            $identifierToken1->getSourceString(),
            $identifierToken2->getSourceString(),
            $identifierToken3->getSourceString()
        );
    }
}
