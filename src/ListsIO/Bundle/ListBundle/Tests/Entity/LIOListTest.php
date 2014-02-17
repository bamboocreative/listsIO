<?php
/**
 * Created by PhpStorm.
 * User: jesserosato
 * Date: 2/16/14
 * Time: 1:08 AM
 */

namespace ListsIO\Bundle\ListBundle\Tests\Entity;

use ListsIO\Bundle\ListBundle\Entity\LIOList;


class LIOListTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var \ListsIO\Bundle\ListBundle\Entity\LIOList
     */
    protected $list;

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
        $this->list = new LIOList();
    }

    public function testEmptyId()
    {

    }

    public function testEmptyTitle()
    {
        $this->assertEquals("", $this->list->getTitle());
    }

    public function testTitleAccessors()
    {
        $this->list->setTitle("Test title");
        $this->assertEquals("Test title", $this->list->getTitle());
    }

    public function testSubtitleEmpty()
    {
        $this->assertEquals("", $this->list->getSubtitle());
    }

    public function testSubtitleAccessors()
    {
        $this->list->setSubtitle("Test subtitle");
        $this->assertEquals("Test subtitle", $this->list->getSubtitle());
    }

    public function testEmptyImageURL()
    {
        $this->assertEquals("", $this->list->getImageURL());
    }

    public function testImageURLAccessors()
    {
        $this->list->setImageURL("http://testurl.com/image.jpg");
        $this->assertEquals("http://testurl.com/image.jpg", $this->list->getImageURL());
    }

    public function testEmptyListItems()
    {
        $this->assertEquals(0, count($this->list->getListItems()));
    }

    public function testAddListItem()
    {
        $listItem = $this->getMockBuilder('ListsIO\Bundle\ListBundle\Entity\LIOListItem')
            ->getMock();

        $this->list->addListItem($listItem);
        $this->assertEquals(1, count($this->list->getListItems()));
    }

    public function testRemoveListItem()
    {
        $listItem = $this->getMockBuilder('ListsIO\Bundle\ListBundle\Entity\LIOListItem')
            ->getMock();

        $this->list->addListItem($listItem);
        $this->assertEquals(1, count($this->list->getListItems()));
        $this->list->removeListItem($listItem);
        $this->assertEquals(0, count($this->list->getListItems()));
    }

    public function testUserAccessors()
    {
        $user = $this->getMockBuilder('\ListsIO\Bundle\UserBundle\Entity\User')
            ->getMock();

        $this->list->setUser($user);
        $this->assertInstanceOf('\ListsIO\Bundle\UserBundle\Entity\User', $this->list->getUser());
    }

    public function testJsonSerialize()
    {
        $this->list->setTitle("Test title");
        $data = $this->list->jsonSerialize();
        $this->assertArrayHasKey("title", $data);
        $this->assertEquals("Test title", $data['title']);
    }

    public function testTimestamping()
    {
        $this->list->prePersistTimestamp();
        $this->assertInstanceOf('\DateTime', $this->list->getCreatedAt());
        $this->assertInstanceOf('\DateTime', $this->list->getUpdatedAt());
        $this->assertGreaterThanOrEqual($this->createdAfter->getTimestamp(), $this->list->getCreatedAt()->getTimestamp());
        $this->assertGreaterThanOrEqual($this->createdAfter->getTimestamp(), $this->list->getUpdatedAt()->getTimestamp());
    }

} 