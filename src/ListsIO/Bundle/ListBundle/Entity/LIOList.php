<?php

namespace ListsIO\Bundle\ListBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use ListsIO\Bundle\UserBundle\Entity\User as User;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\MaxDepth;


/**
 * List
 */
class LIOList
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
     * @var \ListsIO\Bundle\UserBundle\Entity\User
     * @MaxDepth(2)
     */
    private $user;

    /**
     * @var null|LIOList
     * @MaxDepth(1)
     */
    private $nextList;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $listItems;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @MaxDepth(3)
     */
    private $listViews;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @MaxDepth(3)
     */
    private $listLikes;

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
        $this->listItems = new ArrayCollection();
        $this->listViews = new ArrayCollection();
        $this->listLikes = new ArrayCollection();
        $this->title = "";
        $this->subtitle = "";
        $this->imageURL = "";
        $this->nextList = NULL;
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
     * @param string $imageURL
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

    /**
     * @return \ListsIO\Bundle\ListBundle\Entity\LIOList|null
     */
    public function getNextList()
    {
        return $this->nextList;
    }

    /**
     * @param \ListsIO\Bundle\ListBundle\Entity\LIOList|null $nextList
     */
    public function setNextList($nextList)
    {
        $this->nextList = $nextList;
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
     * Add listLikes
     *
     * @param \ListsIO\Bundle\ListBundle\Entity\LIOListLike $listLike
     * @return LIOList
     */
    public function addListLike(LIOListLike $listLike)
    {
        $this->listLikes[] = $listLike;
        $listLike->setList($this);
        return $this;
    }

    /**
     * Remove listLikes
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
     * Add listView
     *
     * @param \ListsIO\Bundle\ListBundle\Entity\LIOListView $listView
     * @return LIOList
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
