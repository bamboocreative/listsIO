<?php

namespace ListsIO\Bundle\ListBundle\Tests\Controller;

use ListsIO\Utilities\Testing\DoctrineWebTestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


// Code adapted from http://dev4theweb.blogspot.com/2012/07/yet-another-look-at-isolated-symfony2.html
class ListsControllerTest extends DoctrineWebTestCase
{
    // protected static $entityManager;
    // protected static $client;
    // protected static $application;

    public function testViewListThrowsResourceNotFoundForNonexistentList()
    {
        $this->getExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
        static::$client->request('GET', '/list/108');
    }

    public function testViewListDoesNotThrowExceptionForListThatShouldExist()
    {
        $crawler = static::$client->request('GET', '/list/1');
        $this->assertGreaterThan(0, $crawler->filter('div.background')->count());

    }
    
}
