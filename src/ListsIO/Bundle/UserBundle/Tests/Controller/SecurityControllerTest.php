<?php

namespace ListsIO\Bundle\UserBundle\Tests\Controller;

use ListsIO\Utilities\Testing\DoctrineWebTestCase;
use ListsIO\Bundle\UserBundle\Entity\User;

class SecurityControllerTest extends DoctrineWebTestCase
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

    public function testLoginRedirectForSecuredRouteForAnonymousUser()
    {
        static::$client->request('GET', '/list/new');
        $this->assertTrue(static::$client->getResponse()->isRedirect());
    }

    public function testLoginDoesNotRedirectToLoginFormForSecuredRoutForAppropriateUser()
    {
        $this->logIn($this->user);
        static::$client->request('GET', '/list/new');
        $this->assertFalse(static::$client->getResponse()->isRedirect('/user/login'));
    }

    public function testLoginFormRedirectsToCheck()
    {
        $crawler = static::$client->request('GET', '/user/login');
        $buttonCrawlerNode = $crawler->selectButton('_submit');
        $form = $buttonCrawlerNode->form(array(
           '_username'  => 'testuser',
           '_password'  => 'test'
        ));
        static::$client->submit($form);
        $this->assertEquals('ListsIO\Bundle\UserBundle\Controller\SecurityController::checkAction',
            static::$client->getRequest()->attributes->get('_controller'));
    }

}
