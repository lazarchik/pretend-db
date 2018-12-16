<?php

namespace Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 */
class User
{
    /**
     * @var int
     * 
     * @ORM\Column(name="userID", type="integer", nullable=false)
     * @ORM\Id
     */
    public $userID;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", nullable=false)
     */
    public $name;
}
