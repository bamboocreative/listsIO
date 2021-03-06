<?php

namespace ListsIO\Bundle\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use ListsIO\Bundle\ListBundle\Entity\LIOList as LIOList;
use ListsIO\Bundle\ListBundle\Entity\LIOListLike;
use ListsIO\Bundle\ListBundle\Entity\LIOListView as LIOListView;
use ListsIO\Bundle\UserBundle\Model\TwitterUserInterface;
use ListsIO\Bundle\UserBundle\Model\FacebookUserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\MaxDepth;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\AccessType;
use JMS\Serializer\Annotation\Accessor;
use JMS\Serializer\Annotation\SerializedName;

/**
 * User
 * @ExclusionPolicy("all")
 */
class User extends BaseUser implements TwitterUserInterface, FacebookUserInterface
{

    /**
     * @var integer
     * @Expose
     */
    protected $id;

    /**
     * @var string
     * @Expose
     * @AccessType("public_method")
     * @Accessor(getter="getProfilePicURL", setter="setProfilePicURL")
     * @SerializedName("profilePicURL")
     */
    protected $profilePicURL;

    private $gravatarURL;

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
     * @Expose
     */
    protected $twitterUsername;

    /**
     * @var string
     */
    protected $facebookId;

    /**
     * @var string
     */
    protected $facebookAccessToken;

    /**
     * @var string
     */
    protected $facebookUsername;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @Expose
     * @MaxDepth(4)
     */
    protected $lists;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @Expose
     * @MaxDepth(3)
     */
    protected $listViews;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @Expose
     * @MaxDepth(3)
     */
    protected $listLikes;

    /**
     * @var \DateTime
     * @Expose
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $updatedAt;

    public function __construct()
    {
        parent::__construct();
        $this->lists = new ArrayCollection();
        $this->listViews = new ArrayCollection();
        $this->listLikes = new ArrayCollection();
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
     * Set the user's Facebook ID
     *
     * @param $facebookId
     */
    public function setFacebookId($facebookId)
    {
        $this->facebookId = $facebookId;
    }

    /**
     * Get the user's Facebook ID
     *
     * @return string
     */
    public function getFacebookId()
    {
        return $this->facebookId;
    }

    /**
     * Set the user's Facebook Access Token
     *
     * @param $facebookAccessToken
     */
    public function setFacebookAccessToken($facebookAccessToken)
    {
        $this->facebookAccessToken = $facebookAccessToken;
    }

    /**
     * Get the user's Facebook Access Token
     *
     * @return string
     */
    public function getFacebookAccessToken()
    {
        return $this->facebookAccessToken;
    }

    /**
     * Set the user's Facebook username
     *
     * @param $facebookUsername
     */
    public function setFacebookUsername($facebookUsername)
    {
        $this->facebookUsername = $facebookUsername;
    }

    /**
     * Get the user's Facebook username
     *
     * @return string
     */
    public function getFacebookUsername()
    {
        return $this->facebookUsername;
    }

    public function setProfilePicURL($url)
    {
        $this->profilePicURL = $url;
    }

    public function getProfilePicURL()
    {
        if (empty($this->profilePicURL)) {
            return $this->getGravatarURL();
        } else {
            return $this->profilePicURL;
        }
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
    protected function getGravatarURL($size = 240, $defaultImageset = 'mm', $maximumRating = 'x')
    {
        if ( empty($this->gravatarURL)) {
            $url = 'http://www.gravatar.com/avatar/';
            $url .= md5(strtolower(trim($this->getEmail())));
            $url .= "?s=$size&d=$defaultImageset&r=$maximumRating";
            $this->gravatarURL = $url;
        }
        return $this->gravatarURL;
    }

    /**
     * Add lists
     *
     * @param \ListsIO\Bundle\ListBundle\Entity\LIOList $list
     * @return User
     */
    public function addList(LIOList $list)
    {
        $this->lists[] = $list;

        return $this;
    }

    /**
     * Remove lists
     *
     * @param \ListsIO\Bundle\ListBundle\Entity\LIOList $list
     */
    public function removeList(LIOList $list)
    {
        $this->lists->removeElement($list);
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
     * Add listView
     *
     * @param \ListsIO\Bundle\ListBundle\Entity\LIOListView $listView
     * @return User
     */
    public function addListView(LIOListView $listView)
    {
        $this->listViews[] = $listView;

        return $this;
    }

    /**
     * Remove listView
     *
     * @param \ListsIO\Bundle\ListBundle\Entity\LIOListView $listView
     */
    public function removeListView(LIOListView $listView)
    {
        $this->listViews->removeElement($listView);
    }

    /**
     * Get listViews
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getListViews()
    {
        return $this->listViews;
    }

    /**
     * @param \ListsIO\Bundle\ListBundle\Entity\LIOListLike $listLike
     * @return $this
     */
    public function addListLike(LIOListLike $listLike)
    {
        $this->listLikes[] = $listLike;

        return $this;
    }

    /**
     * Remove listLike
     *
     * @param \ListsIO\Bundle\ListBundle\Entity\LIOListLike $listLike
     */
    public function removeListLike(LIOListLike $listLike)
    {
        $this->listLikes->removeElement($listLike);
    }

    /**
     * Get listLikes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getListLikes()
    {
        return $this->listLikes;
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

}
