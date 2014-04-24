<?php

namespace ListsIO\Bundle\ListBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use ListsIO\Bundle\UserBundle\Entity\User;
use ListsIO\Bundle\ListBundle\Entity\LIOList;

/**
 * LIOListLike
 */
class LIOListLike
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var LIOList
     */
    protected $list;

    /**
     * @var \DateTime
     */
    private $createdAt;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param LIOList $list
     */
    public function setList(LIOList $list)
    {
        $this->list = $list;
    }

    /**
     * @return LIOList
     */
    public function getList()
    {
        return $this->list;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersistTimestamp()
    {
        $this->createdAt = new \DateTime(date('Y-m-d H:i:s'));
    }

}
