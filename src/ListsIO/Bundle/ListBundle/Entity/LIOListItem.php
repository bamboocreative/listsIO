<?php

namespace ListsIO\Bundle\ListBundle\Entity;

use JsonSerializable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Exception\InvalidArgumentException;

/**
 * ListItem
 */
class LIOListItem implements JsonSerializable
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var int
     */
    protected $orderIndex;

    /**
     * @var \ListsIO\Bundle\ListBundle\Entity\LIOList
     */
    protected $list;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $updatedAt;

    public function __construct()
    {
        $this->title = "";
        $this->description = "";
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
     * @return LIOListItem
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
     * Set description
     *
     * @param string $description
     * @return LIOListItem
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param $index int
     */
    public function setOrderIndex($index) {
        $this->orderIndex = $index;
    }

    public function getOrderIndex() {
        return $this->orderIndex;
    }

    /**
     * Set list
     *
     * @param \ListsIO\Bundle\ListBundle\Entity\LIOList $list
     * @return LIOListItem
     */
    public function setList(\ListsIO\Bundle\ListBundle\Entity\LIOList $list = null)
    {
        $this->list = $list;

        return $this;
    }

    /**
     * Get list
     *
     * @return \ListsIO\Bundle\ListBundle\Entity\LIOList 
     */
    public function getList()
    {
        return $this->list;
    }

    public function jsonSerialize()
    {
        $list = $this->getList();
        $listId = empty($list) ? null : $list->getId();
        return array(
            'id'            => $this->id,
            'title'         => $this->title,
            'description'   => $this->description,
            'listID'        => $listId
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
