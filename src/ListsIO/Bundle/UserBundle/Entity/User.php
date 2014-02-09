<?php

namespace ListsIO\Bundle\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use ListsIO\Bundle\ListBundle\Entity\LIOList as LIOList;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * User
 */
class User extends BaseUser
{
    /**
     * @var integer
     */
    protected $id;

    public function __construct()
    {
        parent::__construct();
        $this->lists = new ArrayCollection();
    }

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
     * @var \Doctrine\Common\Collections\Collection
     */
    private $lists;

    /**
     * Get gravatarURL
     *
     * @return string 
     */
    public function getGravatarURL()
    {
        return $this->createGravatarURL();
    }

    /**
     * Get either a Gravatar URL or complete image tag for a specified email address.
     * string $s Size in pixels, defaults to 80px [ 1 - 2048 ]
     * string $d Default imageset to use [ 404 | mm | identicon | monsterid | wavatar ]
     * string $r Maximum rating (inclusive) [ g | pg | r | x ]
     * @return String containing either just a URL or a complete image tag
     */
    public function createGravatarURL() {
        $s = 240;
        $d = 'mm';
        $r = 'x';
        $url = 'http://www.gravatar.com/avatar/';
        $url .= md5(strtolower(trim($this->getEmail())));
        $url .= "?s=$s&d=$d&r=$r";
        return $url;
    }

    /**
     * Add lists
     *
     * @param \ListsIO\Bundle\ListBundle\Entity\LIOList $lists
     * @return User
     */
    public function addList(LIOList $lists)
    {
        $this->lists[] = $lists;

        return $this;
    }

    /**
     * Remove lists
     *
     * @param \ListsIO\Bundle\ListBundle\Entity\LIOList $lists
     */
    public function removeList(LIOList $lists)
    {
        $this->lists->removeElement($lists);
    }

    /**
     * Get lists
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getLists()
    {
        return $this->lists;
    }
}
