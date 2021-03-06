<?php

namespace PretendDb\Doctrine\Driver\Parser;


use PretendDb\Doctrine\Driver\Parser\Expression\CurrentTimestampExpression;
use PretendDb\Doctrine\Driver\Parser\Expression\ExpressionInterface;
use PretendDb\Doctrine\Driver\Parser\Expression\FunctionCallExpression;
use PretendDb\Doctrine\Driver\Parser\Expression\InsertQueryExpression;
use PretendDb\Doctrine\Driver\Parser\Expression\NullLiteralExpression;
use PretendDb\Doctrine\Driver\Parser\Expression\NumberLiteralExpression;
use PretendDb\Doctrine\Driver\Parser\Expression\SelectExpressionWithOrWithoutAlias;
use PretendDb\Doctrine\Driver\Parser\Expression\SelectQueryExpression;
use PretendDb\Doctrine\Driver\Parser\Expression\SimplePlaceholderExpression;
use PretendDb\Doctrine\Driver\Parser\Expression\StringLiteralExpression;
use PretendDb\Doctrine\Driver\Parser\Expression\TableExpression;
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
    
    public function __construct(Lexer $lexer)
    {
        $this->lexer = $lexer;
        $this->grammar = new Grammar();
    }

    protected function parseExpression(TokenSequence $tokens, int $minPrecedence): ExpressionInterface
    {
        $startTokenIndex = $tokens->getCurrentTokenIndex();
        
        $unaryOperator = $this->grammar->findUnaryOperatorFromToken($tokens->getCurrentToken());
        
        if ($unaryOperator) {
            
            $tokens->advanceCursor(); // Skip the unary operator token.
            
            $operand = $this->parseExpression($tokens, $unaryOperator->getPrecedence());
            $sourceString = $tokens->getSourceText($startTokenIndex, $tokens->getCurrentTokenIndex());
            
            $leftOperand = $unaryOperator->initAST($sourceString, [$operand]);
        } else {
            $leftOperand = $this->parseSimpleExpression($tokens);
        }
        
        $binaryOperator = $this->grammar->findBinaryOperatorFromToken($tokens->getCurrentToken());
        
        while ($binaryOperator && $binaryOperator->getPrecedence() >= $minPrecedence) {
            
            $binaryOperatorToken = $tokens->getCurrentTokenAndAdvanceCursor();
            
            $operatorPrecedence = $binaryOperator->getPrecedence();
            
            $rightOperandMinPrecedence = $operatorPrecedence + ($binaryOperator->isLeftAssociative() ? 1 : 0);
            
            if ($binaryOperatorToken->isIn()) {
                // IN is special: instead of grouping expression list on the right together
                // we're sending them as operands 2, 3, 4, etc.
                $expressionsListOnTheRight = $this->parseExpressionListInParentheses($tokens);
                $allOperands = $expressionsListOnTheRight;
                array_unshift($allOperands, $leftOperand);
                
                $sourceString = $tokens->getSourceText($startTokenIndex, $tokens->getCurrentTokenIndex());
                
                $leftOperand = $binaryOperator->initAST($sourceString, $allOperands);
            } else {
                $rightOperand = $this->parseExpression($tokens, $rightOperandMinPrecedence);
                
                $sourceString = $tokens->getSourceText($startTokenIndex, $tokens->getCurrentTokenIndex());

                $leftOperand = $binaryOperator->initAST($sourceString, [$leftOperand, $rightOperand]);
            }
            
            $binaryOperator = $this->grammar->findBinaryOperatorFromToken($tokens->getCurrentToken());
        }
        
        return $leftOperand;
    }

    public function parse(string $queryString): ExpressionInterface
    {
        $queryTokens = $this->lexer->parse($queryString);
        
        $parsedExpression = $this->parseExpression($queryTokens, 0);
        
        if (!$queryTokens->getCurrentToken()->isInvalidToken()) {
            throw new \RuntimeException(
                "Unexpected token after the end of the expression: ".$queryTokens->getCurrentToken()->dump()
            );
        }
        
        return $parsedExpression;
    }
    
    public function parseInsertQuery(string $queryString): InsertQueryExpression
    {
        $tokens = $this->lexer->parse($queryString);
        
        $parsedInsertQuery = $this->parseInsertExpression($tokens);
        
        $this->ensureEndOfQuery($tokens);
        
        return $parsedInsertQuery;
    }
    
    protected function ensureEndOfQuery(TokenSequence $tokens): void
    {
        if ($tokens->getCurrentToken()->isMinus() && $tokens->getNextToken()->isMinus()) {
            return;
        }
        
        if (!$tokens->getCurrentToken()->isInvalidToken()) {
            throw new \RuntimeException(
                "Unexpected token after the end of the expression: ".$tokens->getCurrentToken()->dump()
            );
        }
    }

    protected function parseSimpleExpression(TokenSequence $tokens): ExpressionInterface
    {
        $currentToken = $tokens->getCurrentToken();
        
        if ($currentToken->isNullLiteral()) {
            $token = $tokens->getCurrentTokenAndAdvanceCursor(); // skip the NULL literal
            
            return new NullLiteralExpression($token->getSourceString());
        }
        
        if ($currentToken->isIdentifier()) {
            if ("CURRENT_TIMESTAMP" == strtoupper($currentToken->getSourceString())) {
                $token = $tokens->getCurrentTokenAndAdvanceCursor(); // skip the CURRENT_TIMESTAMP literal
                return new CurrentTimestampExpression($token->getSourceString());
            }
            
            if ($tokens->getNextToken()->isOpeningParenthesis()) {
                return $this->parseFunctionCallExpression($tokens);
            }
            
            return $this->parseTableField($tokens);
        }
        
        if ($currentToken->isNumberLiteral()) {
            $token = $tokens->getCurrentTokenAndAdvanceCursor();
            
            return new NumberLiteralExpression($token->getSourceString(), (float) $token->getSourceString());
        }
        
        if ($currentToken->isStringLiteral()) {
            
            $token = $tokens->getCurrentTokenAndAdvanceCursor();
        
            return new StringLiteralExpression($token->getSourceString(), (string) $token->getSourceString());
        }
        
        if ($currentToken->isSimplePlaceholder()) {
            $token = $tokens->getCurrentTokenAndAdvanceCursor(); // Skip the simple placeholder token.
            
            return new SimplePlaceholderExpression($token->getSourceString());
        }
        
        if ($currentToken->isOpeningParenthesis()) {
            $tokens->advanceCursor(); // Skip the opening parenthesis token
            
            if ($tokens->getCurrentToken()->isSelect()) {
                return $this->parseSubquery($tokens);
            }
            
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
        
        throw new \RuntimeException("Unknown token in simple expression: ".$tokens->getCurrentToken()->dump()
            .". Tokens: ".$tokens->dump());
    }

    /**
     * SELECT
     *     [ALL | DISTINCT | DISTINCTROW ]
     *       [HIGH_PRIORITY]
     *       [MAX_STATEMENT_TIME = N]
     *       [STRAIGHT_JOIN]
     *       [SQL_SMALL_RESULT] [SQL_BIG_RESULT] [SQL_BUFFER_RESULT]
     *       [SQL_CACHE | SQL_NO_CACHE] [SQL_CALC_FOUND_ROWS]
     *     select_expr [, select_expr ...]
     *     [FROM table_references
     *       [PARTITION partition_list]
     *     [WHERE where_condition]
     *     [GROUP BY {col_name | expr | position}
     *       [ASC | DESC], ... [WITH ROLLUP]]
     *     [HAVING where_condition]
     *     [ORDER BY {col_name | expr | position}
     *       [ASC | DESC], ...]
     *     [LIMIT {[offset,] row_count | row_count OFFSET offset}]
     *     [PROCEDURE procedure_name(argument_list)]
     *     [INTO OUTFILE 'file_name'
     *         [CHARACTER SET charset_name]
     *         export_options
     *       | INTO DUMPFILE 'file_name'
     *       | INTO var_name [, var_name]]
     *     [FOR UPDATE | LOCK IN SHARE MODE]]
     * @param TokenSequence $tokens
     * @return SelectQueryExpression
     */
    protected function parseSubquery(TokenSequence $tokens): SelectQueryExpression
    {
        $startTokenIndex = $tokens->getCurrentTokenIndex();
        
        $subqueryTokens[] = $selectToken = $tokens->getCurrentTokenAndAdvanceCursor();

        if (!$selectToken->isSelect()) {
            throw new \RuntimeException(
                "First token of subquery expression must be SELECT. Got: ".$selectToken->dump()
            );
        }
        
        $selectExpressions = [];
        $fromExpressions = [];
        $whereExpression = null;
        
        do {
            $selectExpressions[] = $this->parseSelectExprPotentiallyWithAlias($tokens);
            
            if (!$tokens->getCurrentToken()->isComma()) {
                break;
            }
            
            $tokens->advanceCursor(); // skip comma
        } while (true);
        
        if ($tokens->getCurrentToken()->isFrom()) {
            $tokens->advanceCursor(); // skip FROM
            
            do {
                $fromExpressions[] = $this->parseTableNamePotentiallyWithAlias($tokens);

                if (!$tokens->getCurrentToken()->isComma()) {
                    break;
                }

                $tokens->advanceCursor(); // skip comma
            } while (true);
        }
        
        if ($tokens->getCurrentToken()->isWhere()) {
            $tokens->advanceCursor(); // skip WHERE
            
            $whereExpression = $this->parseExpression($tokens, 0);
        }
        
        $closingParenthesisToken = $tokens->getCurrentTokenAndAdvanceCursor();
        
        if (!$closingParenthesisToken->isClosingParenthesis()) {
            throw new \RuntimeException(
                "Closing parenthesis expected after subquery, got: ".$closingParenthesisToken->dump()
            );
        }
        
        $sourceString = $tokens->getSourceText($startTokenIndex, $tokens->getCurrentTokenIndex());
        
        return new SelectQueryExpression($sourceString, $selectExpressions, $fromExpressions, $whereExpression);
    }
    
    protected function parseSelectExprPotentiallyWithAlias(TokenSequence $tokens): SelectExpressionWithOrWithoutAlias
    {
        $startTokenIndex = $tokens->getCurrentTokenIndex();
        
        $selectExpression = $this->parseExpression($tokens, 0);
        $selectExprAliasString = $this->parseAliasIfPresent($tokens);
        
        $sourceString = $tokens->getSourceText($startTokenIndex, $tokens->getCurrentTokenIndex());
        
        return new SelectExpressionWithOrWithoutAlias($sourceString, $selectExpression, $selectExprAliasString);
    }
    
    protected function parseTableNamePotentiallyWithAlias(TokenSequence $tokens): TableExpression
    {
        $startTokenIndex = $tokens->getCurrentTokenIndex();
        
        $firstIdentifierToken = $tokens->getCurrentTokenAndAdvanceCursor();
        
        if (!$firstIdentifierToken->isIdentifier()) {
            throw new \RuntimeException("Expected table name, got: ".$firstIdentifierToken->dump());
        }
        
        if (!$tokens->getCurrentToken()->isPeriod()) {
            $alias = $this->parseAliasIfPresent($tokens);
            $sourceString = $tokens->getSourceText($startTokenIndex, $tokens->getCurrentTokenIndex());
            return new TableExpression($sourceString, $firstIdentifierToken->getSourceString(), null, $alias);
        }
        
        $tokens->advanceCursor(); // skip period
        
        $secondIdentifierToken = $tokens->getCurrentTokenAndAdvanceCursor();
        
        if (!$secondIdentifierToken->isIdentifier()) {
            throw new \RuntimeException("Expected table name after period, got: ".$secondIdentifierToken->dump());
        }
        
        $alias = $this->parseAliasIfPresent($tokens);
        
        $sourceString = $tokens->getSourceText($startTokenIndex, $tokens->getCurrentTokenIndex());

        return new TableExpression(
            $sourceString,
            $secondIdentifierToken->getSourceString(),
            $firstIdentifierToken->getSourceString(),
            $alias
        );
    }
    
    protected function parseAliasIfPresent(TokenSequence $tokens): ?string
    {
        if ($tokens->getCurrentToken()->isAs()) {
            $tokens->advanceCursor(); // skip AS

            $aliasToken = $tokens->getCurrentTokenAndAdvanceCursor();

            if (!$aliasToken->isIdentifier()) {
                throw new \RuntimeException(
                    "Identifier is expected after AS. Got: " . $aliasToken->dump()
                );
            }

            return $aliasToken->getSourceString();
        }
        
        if ($tokens->getCurrentToken()->isIdentifier()) {
            $aliasToken = $tokens->getCurrentTokenAndAdvanceCursor();
            
            return $aliasToken->getSourceString();
        }
        
        return null;
    }

    protected function parseFunctionCallExpression(TokenSequence $tokens): FunctionCallExpression
    {
        $startTokenIndex = $tokens->getCurrentTokenIndex();
        
        $functionNameToken = $tokens->getCurrentTokenAndAdvanceCursor();
        
        if (!$functionNameToken->isIdentifier()) {
            throw new \RuntimeException("First token of a function call expression must be an identifier. Got: "
                .$functionNameToken->dump());
        }
        
        $functionArguments = $this->parseExpressionListInParentheses($tokens);
        
        $sourceString = $tokens->getSourceText($startTokenIndex, $tokens->getCurrentTokenIndex());
        
        return new FunctionCallExpression($sourceString, $functionNameToken->getSourceString(), $functionArguments);
    }

    /**
     * @param TokenSequence $tokens
     * @return ExpressionInterface[]
     */
    protected function parseExpressionListInParentheses(TokenSequence $tokens): array
    {
        $openingParenthesisToken = $tokens->getCurrentTokenAndAdvanceCursor();
        
        if (!$openingParenthesisToken->isOpeningParenthesis()) {
            throw new \RuntimeException(
                "Expected opening parenthesis. Got: ".$openingParenthesisToken->dump()
            );
        }
        
        if ($tokens->getCurrentToken()->isClosingParenthesis()) {
            return [];
        }
        
        $expressions = [];
        
        do {
            $expressions[] = $this->parseExpression($tokens, 0);
        
            if (!$tokens->getCurrentToken()->isComma()) {
                break;
            }
            
            $tokens->advanceCursor(); // Skip the comma.
        } while (true);
        
        $closingParenthesisToken = $tokens->getCurrentTokenAndAdvanceCursor();
        
        if (!$closingParenthesisToken->isClosingParenthesis()) {
            throw new \RuntimeException(
                "A comma or a closing parenthesis expected. Got: ".$closingParenthesisToken->dump()
            );
        }
        
        return $expressions;
    }

    protected function parseTableField(TokenSequence $tokens): TableFieldExpression
    {
        $startTokenIndex = $tokens->getCurrentTokenIndex();
        
        $identifierToken1 = $tokens->getCurrentTokenAndAdvanceCursor();
        
        if (!$identifierToken1->isIdentifier()) {
            throw new \RuntimeException("First token of a table field expression must be an identifier. Got: "
                .$identifierToken1->dump());
        }
        
        if (!$tokens->getCurrentToken()->isPeriod()) {
            // No table or database name are present
            
            $sourceString = $tokens->getSourceText($startTokenIndex, $tokens->getCurrentTokenIndex());
            
            return new TableFieldExpression($sourceString, $identifierToken1->getSourceString(), null, null);
        }
        
        $tokens->advanceCursor(); // Skip the period token.
        
        $identifierToken2 = $tokens->getCurrentTokenAndAdvanceCursor();
        
        if (!$identifierToken2->isIdentifier()) {
            throw new \RuntimeException("Second token of a table field expression must be an identifier. Got: "
                .$identifierToken2->dump());
        }
        
        if (!$tokens->getCurrentToken()->isPeriod()) {
            // No database name is present
            
            $sourceString = $tokens->getSourceText($startTokenIndex, $tokens->getCurrentTokenIndex());
            
            return new TableFieldExpression(
                $sourceString,
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
        
        $sourceString = $tokens->getSourceText($startTokenIndex, $tokens->getCurrentTokenIndex());
        
        return new TableFieldExpression(
            $sourceString,
            $identifierToken3->getSourceString(),
            $identifierToken2->getSourceString(),
            $identifierToken1->getSourceString()
        );
    }
    
    protected function parseIdentifier(TokenSequence $tokens): string
    {
        $identifierToken = $tokens->getCurrentTokenAndAdvanceCursor();
        
        if (!$identifierToken->isIdentifier()) {
            throw new \RuntimeException("Identifier expected, got: ".$identifierToken->dump());
        }
        
        return $identifierToken->getSourceString();
    }

    protected function parseInsertExpression(TokenSequence $tokens): InsertQueryExpression
    {
        $startTokenIndex = $tokens->getCurrentTokenIndex();
        
        $isIgnore = false;
        
        $insertToken = $tokens->getCurrentTokenAndAdvanceCursor();
        
        if (!$insertToken->isInsert()) {
            throw new \RuntimeException("INSERT statement should start from INSERT, got: ".$insertToken->dump());
        }
        
        if ($tokens->getCurrentToken()->isIgnore()) {
            $tokens->advanceCursor(); // skip optional IGNORE
            $isIgnore = true;
        }
        
        if ($tokens->getCurrentToken()->isInto()) {
            $tokens->advanceCursor(); // skip optional INTO
        }
        
        $tableName = $this->parseIdentifier($tokens);
        
        $fieldNames = [];
        
        if ($tokens->getCurrentToken()->isOpeningParenthesis()) {
            // List of fields follows
            
            $tokens->advanceCursor(); // skip opening parenthesis
            
            do {
                $fieldNames[] = $this->parseIdentifier($tokens);

                if (!$tokens->getCurrentToken()->isComma()) {
                    break;
                }
                
                $tokens->advanceCursor(); // skip comma
            } while (true);
            
            $closingParenthesisToken = $tokens->getCurrentTokenAndAdvanceCursor();
            if (!$closingParenthesisToken->isClosingParenthesis()) {
                throw new \RuntimeException(
                    "Closing parenthesis expected after fields list, got: ".$closingParenthesisToken->dump()
                );
            }
        }
        
        $valuesToken = $tokens->getCurrentTokenAndAdvanceCursor();
        if (!$valuesToken->isValues()) {
            throw new \RuntimeException("VALUES expected, got: ".$valuesToken->dump());
        }
        
        $valuesLists = [];
        do {
            $valuesLists[] = $this->parseExpressionListInParentheses($tokens);
            
            if (!$tokens->getCurrentToken()->isComma()) {
                break;
            }
            
            $tokens->advanceCursor(); // skip comma
        } while (true);
        
        $sourceString = $tokens->getSourceText($startTokenIndex, $tokens->getCurrentTokenIndex());
        
        return new InsertQueryExpression($sourceString, $tableName, $fieldNames, $valuesLists, $isIgnore);
    }
}
