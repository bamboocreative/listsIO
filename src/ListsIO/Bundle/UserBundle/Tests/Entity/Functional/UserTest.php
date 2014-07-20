<?php

namespace ListsIO\Bundle\UserBundle\Tests\Entity\Functional;

use ListsIO\Bundle\UserBundle\Entity\Follow;
use ListsIO\Tests\DoctrineWebTestCase;
use ListsIO\Bundle\UserBundle\Entity\User;

class UserTest extends DoctrineWebTestCase
{
    // protected static $entityManager;
    // protected static $client;
    // protected static $application;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var User
     */
    protected $user2;

    /**
     * @var User
     */
    protected $user3;

    /**
     * @var Follow
     */
    protected $follow;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        parent::setUp();
        $this->user = static::$entityManager->getRepository('ListsIOUserBundle:User')
            ->find(1);
        $this->user2 = static::$entityManager->getRepository('ListsIOUserBundle:User')
            ->find(2);
        $this->user3 = static::$entityManager->getRepository('ListsIOUserBundle:User')
            ->find(3);
        $this->follow = static::$entityManager->getRepository('ListsIOUserBundle:Follow')
            ->find(1);
    }

    public function testFollows()
    {
        $this->assertTrue($this->user->isFollowing($this->user2));
    }

    public function testFollowedBy()
    {
        $this->assertTrue($this->user2->isFollowedBy($this->user));
    }

}
