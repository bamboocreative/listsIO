<?php
/**
 * Created by PhpStorm.
 * User: jesserosato
 * Date: 2/15/14
 * Time: 11:47 PM
 */

namespace ListsIO\Bundle\UserBundle\Tests\Entity;

use DateTime;
use ListsIO\Bundle\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserTest extends WebTestCase {

    /**
     * @var User
     */
    protected $user;

    /**
     * @var DateTime
     */
    protected $createdAfter;

    public function setUp()
    {
        $this->createdAfter = new \DateTime();
        $this->user = new User();
        $this->user->setEmail("test@example.com");
    }

    public function testGetGravatarURL()
    {
        $url = 'http://www.gravatar.com/avatar/';
        $url .= md5(strtolower(trim("test@example.com")));
        $url .= "?s=240&d=mm&r=x";
        $this->assertEquals($url, $this->user->getGravatarURL(240, 'mm', 'x'));
    }

    public function testEmptyLists()
    {
        $this->assertEquals(0, count($this->user->getLists()));
    }

    public function testAddLists()
    {
        $list = $this->getMock('ListsIO\Bundle\ListBundle\Entity\LIOList');
        $this->user->addList($list);
        $this->assertEquals(1, count($this->user->getLists()));
    }

    public function testRemoveList()
    {
        $list = $this->getMock('ListsIO\Bundle\ListBundle\Entity\LIOList');
        $this->user->addList($list);
        $this->user->removeList($list);
        $this->assertEquals(0, count($this->user->getLists()));
    }

    public function testEmptyListViews()
    {
        $this->assertEquals(0, count($this->user->getListViews()));
    }

    public function testAddListView()
    {
        $listView = $this->getMock('ListsIO\Bundle\ListBundle\Entity\LIOListView');
        $this->user->addListView($listView);
        $this->assertEquals(1, count($this->user->getListViews()));
    }

    public function testRemoveListView()
    {
        $listView = $this->getMock('ListsIO\Bundle\ListBundle\Entity\LIOListView');
        $this->user->addListView($listView);
        $this->assertEquals(1, count($this->user->getListViews()));
        $this->user->removeListView($listView);
        $this->assertEquals(0, count($this->user->getListViews()));
    }

    public function testTimestamping()
    {
        $this->user->prePersistTimestamp();
        $this->assertInstanceOf('\DateTime', $this->user->getCreatedAt());
        $this->assertInstanceOf('\DateTime', $this->user->getUpdatedAt());
        $this->assertGreaterThanOrEqual($this->createdAfter->getTimestamp(),$this->user->getCreatedAt()->getTimestamp());
        $this->assertGreaterThanOrEqual($this->createdAfter->getTimestamp(), $this->user->getUpdatedAt()->getTimestamp());
    }

    public function testJsonSerialize()
    {
        $data = $this->user->jsonSerialize();
        $this->assertArrayHasKey('email', $data);
        $this->assertEquals('test@example.com', $data['email']);
    }
} 