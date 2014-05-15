<?php
/**
 * Created by PhpStorm.
 * User: jesserosato
 * Date: 2/16/14
 * Time: 1:09 AM
 */

namespace ListsIO\Bundle\ListBundle\Tests\Entity;

use ListsIO\Bundle\ListBundle\Entity\LIOListItem as ListItem;


class LIOListItemTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var ListItem
     */
    protected $listItem;

    /**
     * @var \DateTime
     */
    protected $createdAfter;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        $this->createdAfter = new \DateTime();
        $this->listItem = new ListItem();
    }

    public function testEmptyId()
    {
        $this->assertNull($this->listItem->getId());
    }

    public function testEmptyTitle()
    {
        $this->assertEquals("", $this->listItem->getTitle());
    }

    public function testTitleAccessors()
    {
        $this->listItem->setTitle("Test title.");
        $this->assertEquals("Test title.", $this->listItem->getTitle());
    }

    public function testEmptyDescription()
    {
        $this->assertEquals("", $this->listItem->getDescription());
    }

    public function testDescriptionAccessors()
    {
        $this->listItem->setDescription("Test description.");
        $this->assertEquals("Test description.", $this->listItem->getDescription());
    }

    public function testEmptyList()
    {
        $this->assertNull($this->listItem->getList());
    }

    public function testListAccessors()
    {
        $list = $this->getMockBuilder('ListsIO\Bundle\ListBundle\Entity\LIOList')
            ->getMock();

        $list->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));

        $this->listItem->setList($list);
        $this->assertEquals(1, $this->listItem->getList()->getId());
    }

    public function testTimestamping()
    {
        $this->listItem->prePersistTimestamp();
        $this->assertInstanceOf('\DateTime', $this->listItem->getCreatedAt());
        $this->assertInstanceOf('\DateTime', $this->listItem->getUpdatedAt());
        $this->assertGreaterThanOrEqual($this->createdAfter->getTimestamp(), $this->listItem->getCreatedAt()->getTimestamp());
        $this->assertGreaterThanOrEqual($this->createdAfter->getTimestamp(), $this->listItem->getUpdatedAt()->getTimestamp());
    }

} 