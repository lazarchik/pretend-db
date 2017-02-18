<?php
/**
 * @author: Eugene Lazarchik
 * @date: 2/18/17
 */

namespace PretendDb\Doctrine\Driver;


class MySQLColumnMeta
{
    protected $name;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
