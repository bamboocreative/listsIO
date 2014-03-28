<?php

namespace ListsIO\Bundle\ListBundle\Entity;

use JsonSerializable;
use Doctrine\ORM\Mapping as ORM;
use ListsIO\Bundle\UserBundle\Entity\User as User;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * List
 */
class LIOList implements JsonSerializable
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $subtitle;

    /**
     * @var string
     */
    private $imageURL;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $listItems;

    /**
     * @var \ListsIO\Bundle\UserBundle\Entity\User
     */
    private $user;

    public function __construct()
    {
        $this->listItems = new ArrayCollection();
        $this->title = "";
        $this->subtitle = "";
        $this->imageURL = "";
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
     * Set title
     *
     * @param string $title
     * @return LIOList
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set subtitle
     *
     * @param string $subtitle
     * @return LIOList
     */
    public function setSubtitle($subtitle)
    {
        $this->subtitle = $subtitle;

        return $this;
    }

    /**
     * Get subtitle
     *
     * @return string 
     */
    public function getSubtitle()
    {
        return $this->subtitle;
    }

    /**
     * Set image
     *
     * @param string $image
     * @return LIOList
     */
    public function setImageURL($imageURL)
    {
        $this->imageURL = $imageURL;

        return $this;
    }

    /**
     * Get image
     *
     * @return string 
     */
    public function getImageURL()
    {
        return $this->imageURL;
    }

    /**
     * Add listItems
     *
     * @param \ListsIO\Bundle\ListBundle\Entity\LIOListItem $listItem
     * @return LIOList
     */
    public function addListItem(LIOListItem $listItem)
    {
        $this->listItems[] = $listItem;
        $listItem->setOrderIndex(count($this->listItems));
        $listItem->setList($this);
        return $this;
    }

    /**
     * Remove listItems
     *
     * @param \ListsIO\Bundle\ListBundle\Entity\LIOListItem $listItem
     */
    public function removeListItem(LIOListItem $listItem)
    {
        $this->listItems->removeElement($listItem);
    }

    /**
     * Get listItems
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getListItems()
    {
        return $this->listItems;
    }

    /**
     * Set user
     *
     * @param \ListsIO\Bundle\UserBundle\Entity\User $user
     * @return LIOList
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;
        $user->addList($this);

        return $this;
    }

    /**
     * Get user
     *
     * @return \ListsIO\Bundle\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    public function jsonSerialize()
    {
        $listItems = array();
        foreach($this->listItems as $item) {
            $listItems[] = $item->jsonSerialize();
        }
        $user = $this->getUser();
        return array(
            'userID'    => $user ? $user->getId() : null,
            'id'        => $this->getId(),
            'title'     => $this->getTitle(),
            'subtitle'  => $this->getSubtitle(),
            'listItems' => $listItems
        );
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
     * @ORM\PrePersist
     */
    public function prePersistTimestamp()
    {
        $this->updatedAt = new \DateTime(date('Y-m-d H:i:s'));
        if (empty($this->createdAt)) {
            $this->createdAt = new \DateTime(date('Y-m-d H:i:s'));
        }
    }
}
