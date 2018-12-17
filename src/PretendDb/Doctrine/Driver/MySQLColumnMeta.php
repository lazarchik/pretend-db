<?php

namespace PretendDb\Doctrine\Driver;


class MySQLColumnMeta
{
    /** @var string */
    protected $name;
    
    /** @var bool */
    protected $isNullable;
    
    /** @var bool */
    protected $isAutoincremented;
    
    /** @var MySQLColumnInitValue|null */
    protected $defaultValue;
    
    /** @var MySQLColumnInitValue|null */
    protected $onUpdateValue;

    public function __construct(
        string $name,
        bool $isNullable,
        bool $isAutoincremented,
        ?MySQLColumnInitValue $defaultValue,
        ?MySQLColumnInitValue $onUpdateValue
    ) {
        $this->name = $name;
        $this->isNullable = $isNullable;
        $this->isAutoincremented = $isAutoincremented;
        $this->defaultValue = $defaultValue;
        $this->onUpdateValue = $onUpdateValue;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isNullable(): bool
    {
        return $this->isNullable;
    }

    public function isAutoincremented(): bool
    {
        return $this->isAutoincremented;
    }

    /**
     * @return MySQLColumnInitValue|null
     */
    public function getDefaultValue(): ?MySQLColumnInitValue
    {
        return $this->defaultValue;
    }

    /**
     * @return MySQLColumnInitValue|null
     */
    public function getOnUpdateValue()
    {
        return $this->onUpdateValue;
    }
}
