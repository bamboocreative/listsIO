<?php
/**
 * Created by PhpStorm.
 * User: jesserosato
 * Date: 2/16/14
 * Time: 1:09 AM
 */

namespace ListsIO\Bundle\ListBundle\Tests\Entity;

use ListsIO\Bundle\ListBundle\Entity\LIOListLike as ListLike;


class LIOListLikeTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var ListLike
     */
    protected $listLike;

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
        $this->listLike = new ListLike();
    }

    public function testEmptyId()
    {
        $this->assertNull($this->listLike->getId());
    }

    public function testEmptyUser()
    {
        $this->assertNull($this->listLike->getUser());
    }

    public function testUserAccessors()
    {
        $user = $this->getMockBuilder('ListsIO\Bundle\UserBundle\Entity\User')
            ->getMock();
        $this->listLike->setUser($user);
        $this->assertInstanceOf('ListsIO\Bundle\UserBundle\Entity\User', $this->listLike->getUser());
    }

    public function testEmptyList()
    {
        $this->assertNull($this->listLike->getList());
    }

    public function testListAccessors()
    {
        $list = $this->getMockBuilder('ListsIO\Bundle\ListBundle\Entity\LIOList')
            ->getMock();
        $this->listLike->setList($list);
        $this->assertInstanceOf('ListsIO\Bundle\ListBundle\Entity\LIOList', $this->listLike->getList());
    }

    public function testTimestamping()
    {
        $this->listLike->prePersistTimestamp();
        $this->assertInstanceOf('\DateTime', $this->listLike->getCreatedAt());
        $this->assertGreaterThanOrEqual($this->createdAfter->getTimestamp(), $this->listLike->getCreatedAt()->getTimestamp());
    }

} 