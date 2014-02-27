<?php

namespace ListsIO\Bundle\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use ListsIO\Bundle\ListBundle\Entity\LIOList as LIOList;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * User
 */
class User extends BaseUser implements \JsonSerializable, TwitterUserInterface
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $twitterId;

    /**
     * @var string
     */
    protected $twitterAccessToken;

    /**
     * @var string
     */
    protected $twitterUsername;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $lists;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var \DateTime
     */
    private $updatedAt;

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
     * Set the user's Twitter ID
     *
     * @param $twitterId
     */
    public function setTwitterId($twitterId)
    {
        $this->twitterId = $twitterId;
    }

    /**
     * Get the user's Twitter ID
     *
     * @return string
     */
    public function getTwitterId()
    {
        return $this->twitterId;
    }

    /**
     * Set the user's Twitter Access Token
     *
     * @param $twitterAccessToken
     */
    public function setTwitterAccessToken($twitterAccessToken)
    {
        $this->twitterAccessToken = $twitterAccessToken;
    }

    /**
     * Get the user's Twitter Access Token
     *
     * @return string
     */
    public function getTwitterAccessToken()
    {
        return $this->twitterAccessToken;
    }

    /**
     * Set the user's Twitter username
     *
     * @param $twitterUsername
     */
    public function setTwitterUsername($twitterUsername)
    {
        $this->twitterUsername = $twitterUsername;
    }

    /**
     * Get the user's Twitter username
     *
     * @return string
     */
    public function getTwitterUsername()
    {
        return $this->twitterUsername;
    }

    /**
     * Get user's gravatar URL
     * http://gravatar.com
     *
     * @param int $size Size in pixels, defaults to 80px [ 1 - 2048 ]
     * @param string $defaultImageset Default imageset to use [ 404 | mm | identicon | monsterid | wavatar ]
     * @param string $maximumRating Maximum rating (inclusive) [ g | pg | r | x ]
     * @return string
     */
    public function getGravatarURL($size = 240, $defaultImageset = 'mm', $maximumRating = 'x')
    {
        $url = 'http://www.gravatar.com/avatar/';
        $url .= md5(strtolower(trim($this->getEmail())));
        $url .= "?s=$size&d=$defaultImageset&r=$maximumRating";
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

    public function prePersistTimestamp()
    {
        $this->updatedAt = new \DateTime(date('Y-m-d H:i:s'));
        if (empty($this->createdAt)) {
            $this->createdAt = new \DateTime(date('Y-m-d H:i:s'));
        }
    }

    public function jsonSerialize()
    {
        $lists = array();
        foreach( $this->lists as $list ) {
            $lists[] = $list->jsonSerialize();
        }

        return array(
            'id'            => $this->id,
            'username'      => $this->username,
            'email'         => $this->email,
            'gravatarURL'   => $this->getGravatarURL(),
            'createdAt'     => $this->createdAt,
            'updatedAt'     => $this->updatedAt,
            'lists'         => $lists
        );
    }

}
