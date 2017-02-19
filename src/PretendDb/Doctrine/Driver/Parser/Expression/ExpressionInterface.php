<?php
/**
 * @author: Eugene Lazarchik
 * @date: 2/18/17
 */

namespace PretendDb\Doctrine\Driver\Parser\Expression;


interface ExpressionInterface
{
    public function evaluate();

    /**
     * @param string $indentationString
     * @return string
     * @internal param int $indentationLevels
     */
    public function dump($indentationString = "");
}
