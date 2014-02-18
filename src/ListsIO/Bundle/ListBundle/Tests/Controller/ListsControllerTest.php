<?php

namespace ListsIO\Bundle\ListBundle\Tests\Controller;

use ListsIO\Utilities\Testing\DoctrineWebTestCase;

class ListsControllerTest extends DoctrineWebTestCase
{
    // protected static $entityManager;
    // protected static $client;
    // protected static $application;

    public function testViewListThrowsNotFoundHttpExceptionForNonexistentList()
    {
        $this->getExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
        static::$client->request('GET', '/list/108');
    }

    public function testViewListDoesNotThrowExceptionForListThatShouldExist()
    {
        $crawler = static::$client->request('GET', '/list/1');
        $this->assertGreaterThan(0, $crawler->filter('div.background')->count());

    }

    public function testViewListByOwnerRendersEditTemplate()
    {
        $user = static::$entityManager->getRepository('ListsIOUserBundle:User')
            ->find(1);
        $this->logIn($user);
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
        $user = static::$entityManager->getRepository('ListsIOUserBundle:User')
            ->find(1);
        $this->logIn($user);
        static::$client->request('GET', '/list/new');
        $newList = static::$entityManager->getRepository('ListsIOListBundle:LIOList')
            ->find(2);
        $this->assertInstanceOf('ListsIO\Bundle\ListBundle\Entity\LIOList', $newList);
    }

    public function testNewListRedirect()
    {
        $user = static::$entityManager->getRepository('ListsIOUserBundle:User')
            ->find(1);
        $this->logIn($user);
        static::$client->request('GET', '/list/new');
        $this->assertTrue(static::$client->getResponse()->isRedirect('/list/2'));
    }

    public function testNewListItemCreatesListItem()
    {
        $user = static::$entityManager->getRepository('ListsIOUserBundle:User')
            ->find(1);
        $this->logIn($user);
        static::$client->request('GET', '/list/new_item/1', array(), array(), array(
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ));
        $list = static::$entityManager->getRepository('ListsIOListBundle:LIOList')
            ->find(1);
        $this->assertEquals(2, count($list->getListItems()));
    }

    public function testNewListItemJsonResponse()
    {
        $user = static::$entityManager->getRepository('ListsIOUserBundle:User')
            ->find(1);
        $this->logIn($user);
        static::$client->request('GET', '/list/new_item/1', array(), array(), array(
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ));
        $this->assertJsonResponse( static::$client->getResponse(), 200);
    }

    public function testNewListItemJsonHasAppropriateId()
    {
        $user = static::$entityManager->getRepository('ListsIOUserBundle:User')
            ->find(1);
        $this->logIn($user);
        static::$client->request('GET', '/list/new_item/1', array(), array(), array(
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ));
        $raw = static::$client->getResponse()->getContent();
        $data = json_decode($raw);
        $this->assertObjectHasAttribute('id', $data);
        $this->assertEquals(2, $data->id);
    }

    public function testSaveListThrowsNotFoundHttpExceptionForNonexistentUser()
    {
        $this->getExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
        $user = static::$entityManager->getRepository('ListsIOUserBundle:User')
            ->find(1);
        $this->logIn($user);
        static::$client->request(
            'POST',
            '/list/save/108',
            array(),
            array(),
            array(
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            )
        );
    }

    public function testSaveListThrowsNotFoundHttpExceptionForNonexistentList()
    {
        $this->getExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
        $user = static::$entityManager->getRepository('ListsIOUserBundle:User')
            ->find(1);
        $this->logIn($user);
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
    }

    public function testSaveListSavesTitle()
    {
        $user = static::$entityManager->getRepository('ListsIOUserBundle:User')
            ->find(1);
        $this->logIn($user);
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
        $user = static::$entityManager->getRepository('ListsIOUserBundle:User')
            ->find(1);
        $this->logIn($user);
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
        $user = static::$entityManager->getRepository('ListsIOUserBundle:User')
            ->find(1);
        $this->logIn($user);
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

    public function testSaveListItemThrowsNotFoundHttpExceptionForNonexistentList()
    {
        $this->getExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
        $user = static::$entityManager->getRepository('ListsIOUserBundle:User')
            ->find(1);
        $this->logIn($user);
        static::$client->request(
            'POST',
            '/list/save_item/108',
            array(),
            array(),
            array(
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            )
        );
    }

    public function testSaveListItemThrowsNotFoundHttpExceptionForNonexistentListItem()
    {
        $this->getExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
        $user = static::$entityManager->getRepository('ListsIOUserBundle:User')
            ->find(1);
        $this->logIn($user);
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
    }

    public function testSaveListItemSavesTitle()
    {
        $user = static::$entityManager->getRepository('ListsIOUserBundle:User')
            ->find(1);
        $this->logIn($user);
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
        $user = static::$entityManager->getRepository('ListsIOUserBundle:User')
            ->find(1);
        $this->logIn($user);
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
        $user = static::$entityManager->getRepository('ListsIOUserBundle:User')
            ->find(1);
        $this->logIn($user);
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

    public function testRemoveListThrowsNotFoundHttpExceptionForNonexistentList()
    {
        $this->getExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
        $user = static::$entityManager->getRepository('ListsIOUserBundle:User')
            ->find(1);
        $this->logIn($user);
        static::$client->request(
            'POST',
            '/list/remove/108',
            array(),
            array(),
            array(
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            )
        );
    }

    public function testRemoveListRemovesList()
    {
        $user = static::$entityManager->getRepository('ListsIOUserBundle:User')
            ->find(1);
        $this->logIn($user);
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
        $user = static::$entityManager->getRepository('ListsIOUserBundle:User')
            ->find(1);
        $this->logIn($user);
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
        $user = static::$entityManager->getRepository('ListsIOUserBundle:User')
            ->find(1);
        $this->logIn($user);
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
        $user = static::$entityManager->getRepository('ListsIOUserBundle:User')
            ->find(1);
        $this->logIn($user);
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

    public function testRemoveListItemThrowsNotFoundHttpExceptionForNonexistentListItem()
    {
        $this->getExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
        $user = static::$entityManager->getRepository('ListsIOUserBundle:User')
            ->find(1);
        $this->logIn($user);
        static::$client->request(
            'POST',
            '/list/remove_item/108',
            array(),
            array(),
            array(
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            )
        );
    }

    public function testRemoveListItemRemovesListItem()
    {
        $user = static::$entityManager->getRepository('ListsIOUserBundle:User')
            ->find(1);
        $this->logIn($user);
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
        $user = static::$entityManager->getRepository('ListsIOUserBundle:User')
            ->find(1);
        $this->logIn($user);
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
        $user = static::$entityManager->getRepository('ListsIOUserBundle:User')
            ->find(1);
        $this->logIn($user);
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
        $user = static::$entityManager->getRepository('ListsIOUserBundle:User')
            ->find(1);
        $this->logIn($user);
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
