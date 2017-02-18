<?php
/**
 * @author: Eugene Lazarchik
 * @date: 7/7/16
 */

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
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     */
    public $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", nullable=false)
     */
    public $name;
}
