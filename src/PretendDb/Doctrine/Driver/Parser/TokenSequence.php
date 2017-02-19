<?php
/**
 * @author: Eugene Lazarchik
 * @date: 2/18/17
 */

namespace PretendDb\Doctrine\Driver\Parser;


class TokenSequence
{
    /** @var Token[] */
    protected $tokens = [];
    
    public function addToken(Token $token)
    {
        $this->tokens[] = $token;
    }
}
