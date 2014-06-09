<?php

namespace ListsIO\Bundle\ListBundle\Tests\Controller;

use ListsIO\Tests\DoctrineWebTestCase;
use ListsIO\Bundle\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\Response;

class ListsControllerTest extends DoctrineWebTestCase
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
    protected $notOwner;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        parent::setUp();
        $this->user = static::$entityManager->getRepository('ListsIOUserBundle:User')
            ->find(1);
        $this->notOwner = static::$entityManager->getRepository('ListsIOUserBundle:User')
            ->find(2);
    }

    public function testNewListThrows403ForAnonymousUser()
    {
        static::$client->request('GET', '/list');
        $this->assertEquals(403, static::$client->getResponse()->getStatusCode());
    }

    public function testNewListReturns303ForLoggedInUser()
    {
        $this->logIn($this->user);
        static::$client->request('GET', '/list');
        $this->assertEquals(303, static::$client->getResponse()->getStatusCode());
    }

    public function testNewListRedirectsToEditList()
    {
        $this->logIn($this->user);
        static::$client->request('GET', '/list');
        $crawler = static::$client->followRedirect();
        //The edit template has a title input
        $this->assertGreaterThan(0, $crawler->filter('input.list-title')->count());
    }

    public function testNewListCreatesList()
    {
        $this->logIn($this->user);
        static::$client->request('GET', '/list');
        $newList = static::$entityManager->getRepository('ListsIOListBundle:LIOList')
            ->find(2);
        $this->assertInstanceOf('ListsIO\Bundle\ListBundle\Entity\LIOList', $newList);
    }

    public function testEditListThrows404ForNonexistentList()
    {
        static::$client->request('GET', '/list/108/edit');
        $this->assertEquals(404, static::$client->getResponse()->getStatusCode());
    }

    public function testEditListByAnonymousUserThrows403()
    {
        static::$client->request('GET', '/list/1/edit');
        $this->assertEquals(403, static::$client->getResponse()->getStatusCode());
    }

    public function testEditListByNonOwnerThrows403()
    {
        $this->logIn($this->notOwner);
        static::$client->request('GET', '/list/1/edit');
        $this->assertEquals(403, static::$client->getResponse()->getStatusCode());
    }

    public function testEditListByOwnerReturns200()
    {
        $this->logIn($this->user);
        static::$client->request('GET', '/list/1/edit');
        $this->assertEquals(200, static::$client->getResponse()->getStatusCode());
    }

    public function testEditListByOwnerRendersEditTemplate()
    {
        $this->logIn($this->user);
        $crawler = static::$client->request('GET', '/list/1/edit');
        //The edit template has a title input
        $this->assertGreaterThan(0, $crawler->filter('input.list-title')->count());
    }

}
