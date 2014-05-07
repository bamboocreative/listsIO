<?php
/**
 * Created by PhpStorm.
 * User: jesserosato
 * Date: 2/16/14
 * Time: 1:09 AM
 */

namespace ListsIO\Bundle\ListBundle\Tests\Entity;

use ListsIO\Bundle\ListBundle\Entity\LIOListView as ListView;


class LIOListViewTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var ListView
     */
    protected $listView;

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
        $this->listView = new ListView();
    }

    public function testEmptyId()
    {
        $this->assertNull($this->listView->getId());
    }

    public function testEmptyUser()
    {
        $this->assertNull($this->listView->getUser());
    }

    public function testUserAccessors()
    {
        $user = $this->getMockBuilder('ListsIO\Bundle\UserBundle\Entity\UserList')
            ->getMock();
        $this->listView->setUser($user);
        assertInstanceOf('ListsIO\Bundle\UserBundle\Entity\User', $this->listView->getUser());
    }

    public function testAnonymousIdentifierAccessors()
    {
        $this->listViews->setAnonymousIdentifier("user-agent-string", "1234.1234.1234.1234");
        assertEquals("user-agent-string1234.1234.1234.1234", $this->listView->getAnonymousIdentifier());
    }

    public function testEmptyList()
    {
        $this->assertNull($this->listView->getList());
    }

    public function testListAccessors()
    {
        $list = $this->getMockBuilder('ListsIO\Bundle\ListBundle\Entity\LIOList')
            ->getMock();
        $this->listView->setList($list);
        assertInstanceOf('ListsIO\Bundle\ListBundle\Entity\LIOList', $this->listView->getList());
    }

    public function testTimestamping()
    {
        $this->listView->prePersistTimestamp();
        $this->assertInstanceOf('\DateTime', $this->listView->getCreatedAt());
        $this->assertGreaterThanOrEqual($this->createdAfter->getTimestamp(), $this->listView->getCreatedAt()->getTimestamp());
    }

} 