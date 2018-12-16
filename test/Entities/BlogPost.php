<?php

namespace Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="blog_posts")
 */
class BlogPost
{
    /**
     * @var int
     * 
     * @ORM\Column(name="postID", type="integer", nullable=false)
     * @ORM\Id
     */
    public $postID;

    /**
     * @var int 
     * @ORM\Column(name="userID", type="integer", nullable=false)
     */
    public $userID;

    /**
     * @var string
     * @ORM\Column(name="body", type="string", nullable=false)
     */
    public $body;

    /**
     * @var \DateTime
     * @ORM\Column(name="createdAt", type="datetime", nullable=false)
     */
    public $createdAt;
}
