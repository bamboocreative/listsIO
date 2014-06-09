<?php

namespace ListsIO\Bundle\ListBundle\Tests\Controller;

use ListsIO\Tests\DoctrineWebTestCase;
use ListsIO\Bundle\UserBundle\Entity\User;

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

    public function testViewListThrows404ForNonexistentList()
    {
        static::$client->request('GET', '/list/108');
        $this->assertEquals(404, static::$client->getResponse()->getStatusCode());
    }

    public function testViewListDoesNotThrowExceptionForListThatShouldExist()
    {
        $crawler = static::$client->request('GET', '/list/1');
        $this->assertEquals(200, static::$client->getResponse()->getStatusCode());

    }

    public function testViewListJsonResponse()
    {
        static::$client->request(
            'GET',
            '/list/1',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json')
        );
        $this->assertJsonResponse(static::$client->getResponse(), 200);
    }

    public function testViewListJsonHasAppropriateTitle()
    {
        static::$client->request(
            'GET',
            '/list/1',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json')
        );
        $raw = static::$client->getResponse()->getContent();
        $data = json_decode($raw);
        $this->assertObjectHasAttribute('title', $data);
        $this->assertEquals("Test title", $data->title);
    }

    public function testNewListThrows403ForAnonymousUser()
    {
        static::$client->request(
            'GET',
            '/list',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json')
        );
        $this->assertJsonResponse(static::$client->getResponse(), 403);
    }

    public function testNewListReturns201ForLoggedInUser()
    {
        $this->logIn($this->user);
        static::$client->request(
            'GET',
            '/list',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json')
        );
        $this->assertJsonResponse(static::$client->getResponse(), 201);
    }

    public function testNewListCreatesList()
    {
        $this->logIn($this->user);
        static::$client->request(
            'PUT',
            '/list',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json')
        );
        $newList = static::$entityManager->getRepository('ListsIOListBundle:LIOList')
            ->find(2);
        $this->assertInstanceOf('ListsIO\Bundle\ListBundle\Entity\LIOList', $newList);
    }

    public function testNewListJsonHasAppropriateId()
    {

        $this->logIn($this->user);
        static::$client->request(
            'GET',
            '/list',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json')
        );
        $raw = static::$client->getResponse()->getContent();
        $data = json_decode($raw);
        $this->assertObjectHasAttribute('id', $data);
        $this->assertEquals(2, $data->id);
    }

    public function testNewListItemThrows403ForAnonymousUser()
    {
        static::$client->request(
            'GET',
            '/list_item',
            array('listId' => '1'),
            array(),
            array('CONTENT_TYPE' => 'application/json')
        );
        $this->assertJsonResponse(static::$client->getResponse(), 403);
    }

    public function testNewListItemReturns403ForNonOwner()
    {
        $this->logIn($this->notOwner);
        static::$client->request(
            'GET',
            '/list_item',
            array('listId' => '1'),
            array(),
            array('CONTENT_TYPE' => 'application/json')
        );
        $this->assertJsonResponse(static::$client->getResponse(), 403);
    }

    public function testNewListItemReturns201ForListOwner()
    {
        $this->logIn($this->user);
        static::$client->request(
            'GET',
            '/list_item',
            array('listId' => '1'),
            array(),
            array('CONTENT_TYPE' => 'application/json')
        );
        $this->assertJsonResponse(static::$client->getResponse(), 201);
    }

    public function testNewListItemCreatesListItem()
    {
        $this->logIn($this->user);
        static::$client->request(
            'PUT',
            '/list_item',
            array('listId' => '1'),
            array(),
            array('CONTENT_TYPE' => 'application/json')
        );
        $list = static::$entityManager->getRepository('ListsIOListBundle:LIOList')
            ->find(1);
        $this->assertEquals(2, count($list->getListItems()));
    }

    public function testNewListItemJsonHasAppropriateId()
    {

        $this->logIn($this->user);
        static::$client->request(
            'GET',
            '/list_item',
            array('listId' => '1'),
            array(),
            array('CONTENT_TYPE' => 'application/json')
        );
        $raw = static::$client->getResponse()->getContent();
        $data = json_decode($raw);
        $this->assertObjectHasAttribute('id', $data);
        $this->assertEquals(2, $data->id);
    }

    public function testSaveListThrows403ForAnonymousUser()
    {
        static::$client->request(
            'POST',
            '/list/1',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json')
        );
        $this->assertEquals(403, static::$client->getResponse()->getStatusCode());
    }

    public function testSaveListThrows403ForNotOwner()
    {
        $this->logIn($this->notOwner);
        static::$client->request(
            'POST',
            '/list/1',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json')
        );
        $this->assertEquals(403, static::$client->getResponse()->getStatusCode());
    }

    public function testSaveListThrows404ForNonexistentList()
    {
        $this->logIn($this->user);
        static::$client->request(
            'POST',
            '/list/108',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json')
        );
        $this->assertEquals(404, static::$client->getResponse()->getStatusCode());
    }

    public function testSaveListSavesTitle()
    {
        $this->logIn($this->user);
        static::$client->request(
            'POST',
            '/list/1',
            array('title' => "New test title"),
            array(),
            array('CONTENT_TYPE' => 'application/json')
        );
        $list = static::$entityManager->getRepository('ListsIOListBundle:LIOList')
            ->find(1);
        $this->assertEquals("New test title", $list->getTitle());
    }

    public function testSaveListReturns200()
    {
        $this->logIn($this->user);
        static::$client->request(
            'POST',
            '/list/1',
            array('title' => "New test title"),
            array(),
            array('CONTENT_TYPE' => 'application/json')
        );
        $this->assertJsonResponse( static::$client->getResponse(), 200);
    }

    public function testSaveListJsonResponseContainsSavedTitle()
    {
        $this->logIn($this->user);
        static::$client->request(
            'POST',
            '/list/1',
            array('title' => "New test title"),
            array(),
            array('CONTENT_TYPE' => 'application/json')
        );

        $raw = static::$client->getResponse()->getContent();
        $data = json_decode($raw);
        $this->assertObjectHasAttribute('title', $data);
        $this->assertEquals("New test title", $data->title);
    }

    public function testSaveListItemThrows403ForAnonymousUser()
    {
        static::$client->request(
            'POST',
            '/list_item/1',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json')
        );
        $this->assertEquals(403, static::$client->getResponse()->getStatusCode());
    }

    public function testSaveListItemThrows403ForNotUser()
    {
        $this->logIn($this->notOwner);
        static::$client->request(
            'POST',
            '/list_item/1',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json')
        );
        $this->assertEquals(403, static::$client->getResponse()->getStatusCode());
    }

    public function testSaveListItemThrows404ForNonexistentListItem()
    {
        $this->logIn($this->user);
        static::$client->request(
            'POST',
            '/list_item/108',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json')
        );
        $this->assertEquals(404, static::$client->getResponse()->getStatusCode());
    }

    public function testSaveListItemSavesTitle()
    {
        $this->logIn($this->user);
        static::$client->request(
            'POST',
            '/list_item/1',
            array('title' => 'New test title'),
            array(),
            array('CONTENT_TYPE' => 'application/json')
        );
        $list = static::$entityManager->getRepository('ListsIOListBundle:LIOListItem')
            ->find(1);
        $this->assertEquals("New test title", $list->getTitle());
    }

    public function testSaveListItemReturns200()
    {
        $this->logIn($this->user);
        static::$client->request(
            'POST',
            '/list_item/1',
            array('title' => 'New test title'),
            array(),
            array('CONTENT_TYPE' => 'application/json')
        );
        $this->assertJsonResponse( static::$client->getResponse(), 200);
    }

    public function testSaveListItemJsonResponseContainsSavedTitle()
    {
        $this->logIn($this->user);
        static::$client->request(
            'POST',
            '/list_item/1',
            array('title' => 'New test title'),
            array(),
            array('CONTENT_TYPE' => 'application/json')
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
            'DELETE',
            '/list/108',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json')
        );
        $this->assertEquals(404, static::$client->getResponse()->getStatusCode());
    }

    public function testRemoveListThrows403ForAnonymousUser()
    {
        static::$client->request(
            'DELETE',
            '/list/1',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json')
        );
        $this->assertEquals(403, static::$client->getResponse()->getStatusCode());
    }

    public function testRemoveListThrows403ForNotOwner()
    {
        $this->logIn($this->notOwner);
        static::$client->request(
            'DELETE',
            '/list/1',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json')
        );
        $this->assertEquals(403, static::$client->getResponse()->getStatusCode());
    }

    public function testRemoveListRemovesList()
    {
        $this->logIn($this->user);
        static::$client->request(
            'DELETE',
            '/list/1',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json')
        );
        $list = static::$entityManager->find('ListsIOListBundle:LIOList', 1);
        $this->assertNull($list);
    }

    public function testRemoveListReturns204()
    {
        $this->logIn($this->user);
        static::$client->request(
            'DELETE',
            '/list/1',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json')
        );
        $this->assertJsonResponse( static::$client->getResponse(), 204);
    }

    public function testRemoveListItemThrows404ForNonexistentListItem()
    {
        $this->logIn($this->user);
        static::$client->request(
            'DELETE',
            '/list_item/108',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json')
        );
        $this->assertEquals(404, static::$client->getResponse()->getStatusCode());
    }

    public function testRemoveListItemThrows403ForAnonymousUser()
    {
        static::$client->request(
            'DELETE',
            '/list_item/1',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json')
        );
        $this->assertEquals(403, static::$client->getResponse()->getStatusCode());
    }

    public function testRemoveListItemThrows403ForNotOwner()
    {
        $this->logIn($this->notOwner);
        static::$client->request(
            'DELETE',
            '/list_item/1',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json')
        );
        $this->assertEquals(404, static::$client->getResponse()->getStatusCode());
    }

    public function testRemoveListItemRemovesListItem()
    {
        $this->logIn($this->user);
        static::$client->request(
            'DELETE',
            '/list_item/1',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json')
        );
        $list = static::$entityManager->getRepository('ListsIOListBundle:LIOList')
            ->find(1);
        $this->assertEquals(0, count($list->getListItems()));
    }

    public function testRemoveListItemReturns204()
    {
        $this->logIn($this->user);
        static::$client->request(
            'DELETE',
            '/list_item/1',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json')
        );
        $this->assertJsonResponse(static::$client->getResponse(), 204);
    }

    public function testNewListLikeReturns403ForAnonymousUser()
    {
        static::$client->request(
            'PUT',
            'list_like',
            array('listId' => '1'),
            array(),
            array('CONTENT_TYPE' => 'application/json')
        );
        $this->assertJsonResponse(static::$client->getResponse(), 403);
    }

    public function testNewListLikeReturns201()
    {
        $this->logIn($this->notOwner);
        static::$client->request(
            'PUT',
            'list_like',
            array('listId' => '1'),
            array(),
            array('CONTENT_TYPE' => 'application/json')
        );
        $this->assertJsonResponse(static::$client->getResponse(), 201);
    }

    public function testNewListLikeThrows409ForAlreadyLikedList()
    {
        $this->logIn($this->notOwner);
        static::$client->request(
            'PUT',
            'list_like',
            array('listId' => '1'),
            array(),
            array('CONTENT_TYPE' => 'application/json')
        );
        static::$client->request(
            'PUT',
            'list_like',
            array('listId' => '1'),
            array(),
            array('CONTENT_TYPE' => 'application/json')
        );
        $this->assertJsonResponse(static::$client->getResponse(), 409);
    }

    public function testPopularListsReturns200()
    {
        static::$client->request('GET', 'lists/popular');
        $this->assertEquals(200, static::$client->getResponse()->getStatusCode());
    }

    public function testPopularListsReturnsArrayOfListsSortedByPopularity()
    {
        static::$client->request(
            'GET',
            'lists/popular',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json')
        );
        $raw = static::$client->getResponse()->getContent();
        $data = json_decode($raw);
        $this->assertNotEmpty($data[0]);
        $this->assertNotEmpty($data[1]);
        $this->assertObjectHasAttribute('id', $data[0]);
        // The first list is the only one with views/likes attached.
        $this->assertEquals(1, $data[0]->id);
    }

}
