<?php

namespace ListsIO\Bundle\ListBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use ListsIO\Bundle\UserBundle\Entity\User as User;
use Doctrine\Common\Collections\ArrayCollection;

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

}
