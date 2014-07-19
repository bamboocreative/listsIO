<?php

namespace ListsIO\Bundle\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\MaxDepth;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;

/**
 * User
 * @ExclusionPolicy("all")
 */
class Follow
{

    /**
     * @var integer
     * @Expose
     */
    protected $id;

    /**
     * @var User
     * @Expose
     * @MaxDepth(3)
     */
    protected $follower;

    /**
     * @var User
     * @Expose
     * @MaxDepth(3)
     */
    protected $followed;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $updatedAt;

    /**
     * @param User $follower
     * @return $this
     */
    public function setFollower(User $follower)
    {
        $this->follower = $follower;
        return $this;
    }

    /**
     * @return User
     */
    public function getFollower()
    {
        return $this->follower;
    }

    /**
     * @param User $followed
     * @return $this
     */
    public function setFollowed(User $followed)
    {
        $this->followed = $followed;
        return $this;
    }

    /**
     * @return User
     */
    public function getFollowed()
    {
        return $this->followed;
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
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Timestamp the object before persisting it.
     */
    public function prePersistTimestamp()
    {
        $this->updatedAt = new \DateTime(date('Y-m-d H:i:s'));
        if (empty($this->createdAt)) {
            $this->createdAt = new \DateTime(date('Y-m-d H:i:s'));
        }
    }

    /**
     * Remove self from relationships before delete.
     */
    public function preRemoveCleanup()
    {
        $this->follower->removeFollow($this);
        $this->followed->removeFollowedBy($this);
    }


}
