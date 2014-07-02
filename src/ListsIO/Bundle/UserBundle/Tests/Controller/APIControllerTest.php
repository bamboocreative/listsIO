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
     * {@inheritDoc}
     */
    public function setUp()
    {
        parent::setUp();
        $this->user = static::$entityManager->getRepository('ListsIOUserBundle:User')
            ->find(1);
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

}
