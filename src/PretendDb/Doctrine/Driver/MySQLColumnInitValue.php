<?php

namespace PretendDb\Doctrine\Driver;


class MySQLColumnInitValue
{
    /** @var bool */
    protected $isCurrentTimestamp;
    
    /** @var mixed */
    protected $value;

    /**
     * @param bool $isCurrentTimestamp
     * @param mixed $value
     */
    public function __construct(bool $isCurrentTimestamp, $value)
    {
        $this->isCurrentTimestamp = $isCurrentTimestamp;
        $this->value = $value;
    }

    public function isCurrentTimestamp(): bool
    {
        return $this->isCurrentTimestamp;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
