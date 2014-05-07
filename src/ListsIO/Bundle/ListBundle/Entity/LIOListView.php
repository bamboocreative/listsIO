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
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

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
     * Used for tracking views by anonymous users.
     *
     * @var string
     */
    protected $anonymousIdentifier;

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
     * @param string $userAgent
     * @param string $userIP
     */
    public function setAnonymousIdentifier($userAgent, $userIP)
    {
        // Only generate an anonymous identifier if:
        // 1. The user agent exists.
        // 2. The user IP address exists.
        if ( empty($userAgent) || empty($userIP)) {
            $this->anonymousIdentifier = null;
        }
        // Max anonymous identifier length is 512 (arbitrary limitation on entity config).
        $userAgent = substr($userAgent, 0, (511 - strlen($userIP)));
        $this->anonymousIdentifier = $userAgent . $userIP;
    }

    /**
     * @return string
     */
    public function getAnonymousIdentifier()
    {
        return $this->anonymousIdentifier;
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
     * Timestamp the object before saving.
     */
    public function prePersistTimestamp()
    {
        $this->createdAt = new \DateTime(date('Y-m-d H:i:s'));
    }

} 