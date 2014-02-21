<?php

namespace ListsIO\Bundle\ListBundle\Tests\Controller;

use ListsIO\Utilities\Testing\DoctrineWebTestCase;
use ListsIO\Bundle\UserBundle\Entity\User;

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
     * {@inheritDoc}
     */
    public function setUp()
    {
        parent::setUp();
        $this->user = static::$entityManager->getRepository('ListsIOUserBundle:User')
            ->find(1);
    }

    public function testViewListThrows404ForNonexistentList()
    {
        static::$client->request('GET', '/list/108');
        $this->assertEquals(404, static::$client->getResponse()->getStatusCode());
    }

    public function testViewListDoesNotThrowExceptionForListThatShouldExist()
    {
        $crawler = static::$client->request('GET', '/list/1');
        $this->assertGreaterThan(0, $crawler->filter('div.background')->count());

    }

    public function testViewListByOwnerRendersEditTemplate()
    {
        $this->logIn($this->user);
        $crawler = static::$client->request('GET', '/list/1');
        // Only the owner template has the title input
        $this->assertGreaterThan(0, $crawler->filter('input#title')->count());
    }

    public function testViewListByNonOwnerDoesNotRenderEditTemplate()
    {
        $crawler = static::$client->request('GET', '/list/1');
        // Only the owner template has the title input
        $this->assertEquals(0, $crawler->filter('input#title')->count());
    }

    public function testViewListJsonResponse()
    {
        static::$client->request('GET', '/list/1.json');
        $this->assertJsonResponse( static::$client->getResponse(), 200);
    }

    public function testViewListJsonHasAppropriateTitle()
    {
        static::$client->request('GET', '/list/1.json');
        $raw = static::$client->getResponse()->getContent();
        $data = json_decode($raw);
        $this->assertObjectHasAttribute('title', $data);
        $this->assertEquals("Test title", $data->title);
    }

    public function testNewListCreatesList()
    {
        $this->logIn($this->user);
        static::$client->request('GET', '/list/new');
        $newList = static::$entityManager->getRepository('ListsIOListBundle:LIOList')
            ->find(2);
        $this->assertInstanceOf('ListsIO\Bundle\ListBundle\Entity\LIOList', $newList);
    }

    public function testNewListRedirect()
    {
        $this->logIn($this->user);
        static::$client->request('GET', '/list/new');
        $this->assertTrue(static::$client->getResponse()->isRedirect('/list/2'));
    }

    public function testNewListItemCreatesListItem()
    {
        $this->logIn($this->user);
        static::$client->request('GET', '/list/new_item/1', array(), array(), array(
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ));
        $list = static::$entityManager->getRepository('ListsIOListBundle:LIOList')
            ->find(1);
        $this->assertEquals(2, count($list->getListItems()));
    }

    public function testNewListItemJsonResponse()
    {
        $this->logIn($this->user);
        static::$client->request('GET', '/list/new_item/1', array(), array(), array(
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ));
        $this->assertJsonResponse( static::$client->getResponse(), 200);
    }

    public function testNewListItemJsonHasAppropriateId()
    {

        $this->logIn($this->user);
        static::$client->request('GET', '/list/new_item/1', array(), array(), array(
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ));
        $raw = static::$client->getResponse()->getContent();
        $data = json_decode($raw);
        $this->assertObjectHasAttribute('id', $data);
        $this->assertEquals(2, $data->id);
    }

    public function testSaveListThrows404ForNonexistentUser()
    {
        $this->logIn($this->user);
        static::$client->request(
            'POST',
            '/list/save/108',
            array(),
            array(),
            array(
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            )
        );
        $this->assertEquals(404, static::$client->getResponse()->getStatusCode());
    }

    public function testSaveListThrows404ForNonexistentList()
    {
        $this->logIn($this->user);
        static::$client->request(
            'POST',
            '/list/save/1',
            array(
                'id' => 108
            ),
            array(),
            array(
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            )
        );
        $this->assertEquals(404, static::$client->getResponse()->getStatusCode());
    }

    public function testSaveListSavesTitle()
    {
        $this->logIn($this->user);
        static::$client->request(
            'POST',
            '/list/save/1',
            array(
                'id' => 1,
                'title' => "New test title"
            ),
            array(),
            array(
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            )
        );
        $list = static::$entityManager->getRepository('ListsIOListBundle:LIOList')
            ->find(1);
        $this->assertEquals("New test title", $list->getTitle());
    }

    public function testSaveListJsonResponse()
    {
        $this->logIn($this->user);
        static::$client->request(
            'POST',
            '/list/save/1',
            array(
                'id' => 1,
                'title' => "New test title"
            ),
            array(),
            array(
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            )
        );
        $this->assertJsonResponse( static::$client->getResponse(), 200);
    }

    public function testSaveListJsonResponseContainsSavedTitle()
    {
        $this->logIn($this->user);
        static::$client->request(
            'POST',
            '/list/save/1',
            array(
                'id' => 1,
                'title' => "New test title"
            ),
            array(),
            array(
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            )
        );

        $raw = static::$client->getResponse()->getContent();
        $data = json_decode($raw);
        $this->assertObjectHasAttribute('title', $data);
        $this->assertEquals("New test title", $data->title);
    }

    public function testSaveListItemThrows404ForNonexistentList()
    {
        $this->logIn($this->user);
        static::$client->request(
            'POST',
            '/list/save_item/108',
            array(),
            array(),
            array(
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            )
        );
        $this->assertEquals(404, static::$client->getResponse()->getStatusCode());
    }

    public function testSaveListItemThrows404ForNonexistentListItem()
    {
        $this->logIn($this->user);
        static::$client->request(
            'POST',
            '/list/save_item/1',
            array(
                'id' => 108
            ),
            array(),
            array(
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            )
        );
        $this->assertEquals(404, static::$client->getResponse()->getStatusCode());
    }

    public function testSaveListItemSavesTitle()
    {
        $this->logIn($this->user);
        static::$client->request(
            'POST',
            '/list/save_item/1',
            array(
                'id' => 1,
                'title' => "New test title"
            ),
            array(),
            array(
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            )
        );
        $list = static::$entityManager->getRepository('ListsIOListBundle:LIOListItem')
            ->find(1);
        $this->assertEquals("New test title", $list->getTitle());
    }

    public function testSaveListItemJsonResponse()
    {
        $this->logIn($this->user);
        static::$client->request(
            'POST',
            '/list/save_item/1',
            array(
                'id' => 1,
                'title' => "New test title"
            ),
            array(),
            array(
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            )
        );
        $this->assertJsonResponse( static::$client->getResponse(), 200);
    }

    public function testSaveListItemJsonResponseContainsSavedTitle()
    {
        $this->logIn($this->user);
        static::$client->request(
            'POST',
            '/list/save_item/1',
            array(
                'id' => 1,
                'title' => "New test title"
            ),
            array(),
            array(
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            )
        );

        $raw = static::$client->getResponse()->getContent();
        $data = json_decode($raw);
        $this->assertObjectHasAttribute('title', $data);
        $this->assertEquals("New test title", $data->title);
    }

    public function testRemoveListThrows404ForNonexistentList()
    {
        $this->logIn($this->user);
        static::$client->request(
            'POST',
            '/list/remove/108',
            array(),
            array(),
            array(
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            )
        );
        $this->assertEquals(404, static::$client->getResponse()->getStatusCode());
    }

    public function testRemoveListRemovesList()
    {
        $this->logIn($this->user);
        static::$client->request(
            'POST',
            '/list/remove/1',
            array(),
            array(),
            array(
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            )
        );
        $list = static::$entityManager->find('ListsIOListBundle:LIOList', 1);
        $this->assertNull($list);
    }

    public function testRemoveListJsonResponse()
    {
        $this->logIn($this->user);
        static::$client->request(
            'POST',
            '/list/remove/1',
            array(),
            array(),
            array(
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            )
        );
        $this->assertJsonResponse( static::$client->getResponse(), 200);
    }

    public function testRemoveListJsonResponseContainsSuccessMessage()
    {
        $this->logIn($this->user);
        static::$client->request(
            'POST',
            '/list/remove/1',
            array(),
            array(),
            array(
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            )
        );

        $raw = static::$client->getResponse()->getContent();
        $data = json_decode($raw);
        $this->assertObjectHasAttribute('success', $data);
        $this->assertEquals(TRUE, $data->id);
    }

    public function testRemoveListJsonResponseContainsRemovedId()
    {
        $this->logIn($this->user);
        static::$client->request(
            'POST',
            '/list/remove/1',
            array(),
            array(),
            array(
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            )
        );

        $raw = static::$client->getResponse()->getContent();
        $data = json_decode($raw);
        $this->assertObjectHasAttribute('id', $data);
        $this->assertEquals(1, $data->id);
    }

    public function testRemoveListItemThrows404ForNonexistentListItem()
    {
        $this->logIn($this->user);
        static::$client->request(
            'POST',
            '/list/remove_item/108',
            array(),
            array(),
            array(
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            )
        );
        $this->assertEquals(404, static::$client->getResponse()->getStatusCode());
    }

    public function testRemoveListItemRemovesListItem()
    {
        $this->logIn($this->user);
        static::$client->request(
            'POST',
            '/list/remove_item/1',
            array(),
            array(),
            array(
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            )
        );
        $list = static::$entityManager->getRepository('ListsIOListBundle:LIOList')
            ->find(1);
        $this->assertEquals(0, count($list->getListItems()));
    }

    public function testRemoveListItemJsonResponse()
    {
        $this->logIn($this->user);
        static::$client->request(
            'POST',
            '/list/remove_item/1',
            array(),
            array(),
            array(
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            )
        );
        $this->assertJsonResponse( static::$client->getResponse(), 200);
    }

    public function testRemoveListItemJsonResponseContainsSuccessMessage()
    {
        $this->logIn($this->user);
        static::$client->request(
            'POST',
            '/list/remove_item/1',
            array(),
            array(),
            array(
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            )
        );

        $raw = static::$client->getResponse()->getContent();
        $data = json_decode($raw);
        $this->assertObjectHasAttribute('success', $data);
        $this->assertEquals(TRUE, $data->id);
    }

    public function testRemoveListItemJsonResponseContainsRemovedId()
    {
        $this->logIn($this->user);
        static::$client->request(
            'POST',
            '/list/remove_item/1',
            array(),
            array(),
            array(
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            )
        );

        $raw = static::$client->getResponse()->getContent();
        $data = json_decode($raw);
        $this->assertObjectHasAttribute('id', $data);
        $this->assertEquals(1, $data->id);
    }

}
