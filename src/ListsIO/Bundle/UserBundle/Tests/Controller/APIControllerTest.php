<?php

namespace ListsIO\Bundle\UserBundle\Tests\Controller;

use ListsIO\Tests\DoctrineWebTestCase;
use ListsIO\Bundle\UserBundle\Entity\User;
use Symfony\Component\HttpKernel\Exception\HttpException;

class APIControllerTest extends DoctrineWebTestCase
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
     * {@inheritDoc}
     */
    public function setUp()
    {
        parent::setUp();
        $this->user = static::$entityManager->getRepository('ListsIOUserBundle:User')
            ->find(1);
        $this->user2 = static::$entityManager->getRepository('ListsIOUserBundle:User')
            ->find(2);
    }

    public function testUserByIdThrows404ForNonexistentUser()
    {
        static::$client->request('GET', '/user/108');
        $this->assertEquals(404, static::$client->getResponse()->getStatusCode());
    }

    public function testUserByIdDoesNotThrowExceptionForUserThatShouldExist()
    {
        static::$client->request('GET', '/user/1');
        $this->assertEquals(200, static::$client->getResponse()->getStatusCode());
    }

    public function testUserByIdJsonResponse()
    {
        static::$client->request('GET', '/user/1.json');
        $this->assertJsonResponse(static::$client->getResponse(), 200);
    }

    public function testUserByIdJsonHasAppropriateId()
    {
        static::$client->request('GET', '/user/1.json');
        $raw = static::$client->getResponse()->getContent();
        $data = json_decode($raw);
        $this->assertObjectHasAttribute('id', $data);
        $this->assertEquals(1, $data->id);
    }

    public function testNewFollowReturns409ForExistingFollow()
    {
        $this->logIn($this->user);
        static::$client->request('POST', '/follow', array('followedId' => 2));
        $this->assertJsonResponse(static::$client->getResponse(), 409);
    }

    public function testNewFollowReturns201ForValidData()
    {
        $this->logIn($this->user);
        static::$client->request('POST', '/follow', array('followedId' => 3));
        $this->assertJsonResponse(static::$client->getResponse(), 201);
    }

    public function testNewFollowJsonHasAppropriateData()
    {
        $this->logIn($this->user);
        static::$client->request('POST', '/follow', array('followedId' => 3));
        $raw = static::$client->getResponse()->getContent();
        $data = json_decode($raw);
        $this->assertNotEmpty($data->followed->id);
        $this->assertEquals(3, $data->followed->id);
    }

    public function testNewFollowReturns404ForNonexistentFollowed()
    {
        $this->logIn($this->user);
        static::$client->request('POST', '/follow', array('followedId' => 108));
        $this->assertJsonResponse(static::$client->getResponse(), 404);
    }

    public function testRemoveFollowReturns404ForNonexistentFollow()
    {
        $this->logIn($this->user);
        static::$client->request('DELETE', '/follow/2');
        $this->assertJsonResponse(static::$client->getResponse(), 404);
    }

    public function testRemoveFollowReturns403ForNonOwner()
    {
        $this->logIn($this->user2);
        static::$client->request('DELETE', '/follow', array('followedId' => 2));
        $this->assertJsonResponse(static::$client->getResponse(), 403);
    }

    public function testRemoveFollowReturns204ForValidFollowId()
    {
        $this->logIn($this->user);
        static::$client->request('DELETE', '/follow/1');
        $this->assertJsonResponse(static::$client->getResponse(), 204);
    }

    public function testRemoveFollowReturns204ForValidFollowedId()
    {
        $this->logIn($this->user);
        static::$client->request('DELETE', '/follow', array('followedId' => 2));
        $this->assertJsonResponse(static::$client->getResponse(), 204);
    }

}
