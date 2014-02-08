<?php

namespace ListsIO\Bundle\ListBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ListItem
 */
class LIOListItem
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
    private $description;

    /**
     * @var \ListsIO\Bundle\ListBundle\Entity\LIOList
     */
    private $list;


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
}
