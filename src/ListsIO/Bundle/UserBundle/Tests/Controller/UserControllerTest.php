<?php

namespace ListsIO\Bundle\UserBundle\Tests\Controller;

use ListsIO\Utilities\Testing\DoctrineWebTestCase;
use ListsIO\Bundle\UserBundle\Entity\User;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UserControllerTest extends DoctrineWebTestCase
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

    public function testIndexRedirectsAnonymousUserToRegister()
    {
        static::$client->request('GET', '/');
        $this->assertTrue(static::$client->getResponse()->isRedirect('/user/register/'));
    }

    public function testIndexRedirectsAuthenticatedUserToOwnProfile()
    {
        $this->logIn($this->user);
        static::$client->request('GET', '/');
        $this->assertTrue(static::$client->getResponse()->isRedirect('/testuser'));
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

    public function testUserByUsernameThrows404ForNonexistentUser()
    {
        static::$client->request('GET', '/fakeusername');
        $this->assertEquals(404, static::$client->getResponse()->getStatusCode());
    }

    public function testUserByUsernameDoesNotThrowExceptionForUserThatShouldExist()
    {
        static::$client->request('GET', '/testuser');
        $this->assertEquals(200, static::$client->getResponse()->getStatusCode());
    }

    // TODO: Test JSON routes.
}
