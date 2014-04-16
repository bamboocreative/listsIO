<?php
/**
 * Created by PhpStorm.
 * User: jesserosato
 * Date: 4/2/14
 * Time: 3:36 PM
 */

namespace ListsIO\Bundle\ListBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use ListsIO\Bundle\UserBundle\Entity\User;

class LIOListView {

    /**
     * @var int
     */
    protected $id;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var LIOList
     */
    protected $list;

    /**
     * @var DateTime
     */
    protected $createdAt;

    /**
     * @return int
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
     * @return DateTime
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