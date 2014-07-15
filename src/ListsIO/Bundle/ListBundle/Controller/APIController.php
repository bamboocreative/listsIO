<?php

namespace ListsIO\Bundle\ListBundle\Controller;

use ListsIO\Bundle\ListBundle\Entity\LIOList;
use ListsIO\Bundle\ListBundle\Entity\LIOListItem;
use ListsIO\Bundle\ListBundle\Entity\LIOListLike;
use ListsIO\Bundle\ListBundle\Entity\LIOListView;
use ListsIO\Bundle\UserBundle\Entity\User;
use ListsIO\Bundle\ListBundle\Controller\Controller as BaseController;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

// TODO: REST API (INCLUDING AUTHENTICATION!)
class APIController extends BaseController
{

    public function viewListAction(Request $request, $id)
    {
        $format = $request->getRequestFormat();

        /**
         * @var $list LIOList
         */
        $list = $this->loadList($id);

        // Set the target path so user's can be redirected here after login (esp. when trying to like list).
        $this->get('session')->set('target_path', $this->get('router')->generate('lists_io_view_list', array('id' => $id)));

        $user = $this->getUser();
        $this->newListView($request, $list, $user);
        $liked = $this->listIsLikedByUser($list, $user);

        // Prep template data.
        $data = array(
            'list'          => $this->serialize($list, $format),
            'liked'         => $liked,
        );
        return $this->render('ListsIOListBundle:API:list.'.$format.'.twig', $data);
    }

    public function newListAction()
    {
        $list = $this->newList();
        return $this->jsonResponse($list, Response::HTTP_CREATED);
    }

    public function newListItemAction(Request $request)
    {
        $list = $this->loadList($request->request->get('listId'));
        $this->requireUserIsObjectOwner($list);
        $listItem = new LIOListItem();
        $list->addListItem($listItem);
        $this->saveEntity($list);
        return $this->jsonResponse($listItem, Response::HTTP_CREATED);
    }

    /**
     * @param Request $request
     * @param $id
     * @return Response
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function saveListAction(Request $request, $id)
    {
        $data = $request->request->all();
        $list = $this->loadList($id);
        $this->requireUserIsObjectOwner($list);
        $this->deserialize($list, $data);
        $this->saveEntity($list);
        $list = $this->serialize($list);
        // POST should return 200.
        return $this->render('ListsIOListBundle:API:list.json.twig', array('list' => $list));
    }

    /**
     * @param Request $request
     * @param $itemId
     * @return Response
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function saveListItemAction(Request $request, $itemId)
    {
        $data = $request->request->all();
        $this->get('logger')->error(print_r($data, true));
        /** @var LIOListItem $listItem */
        $listItem = $this->loadListItem($itemId);
        $this->requireUserIsObjectOwner($listItem->getList());
        $this->deserialize($listItem, $data);
        $this->saveEntity($listItem);
        $listItem = $this->serialize($listItem);
        // PUT should return 200.
        return $this->render('ListsIOListBundle:API:listItem.json.twig', array('listItem' => $listItem));
    }

    /**
     * @param Request $request
     * @param $listId
     * @return Response
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function removeListAction(Request $request, $listId)
    {
        $list = $this->loadList($listId);
        $this->requireUserIsObjectOwner($list);
        $this->removeEntity($list);
        return $this->jsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param Request $request
     * @param $itemId
     * @return Response
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function removeListItemAction(Request $request, $itemId)
    {
        $listItem = $this->loadListItem($itemId);
        $list = $listItem->getList();
        $this->requireUserIsObjectOwner($list);
        $list->removeListItem($listItem);
        $this->removeEntity($listItem);
        $this->saveEntity($list);
        return $this->jsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function newListLikeAction(Request $request)
    {
        $listId = $request->request->get('listId');
        $like = $this->newListLike($this->loadList($listId));
        return $this->jsonResponse($like, Response::HTTP_CREATED);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function viewPopularListsAction(Request $request)
    {
        $limit = $request->request->get('limit', 10);

        $em = $this->getDoctrine()->getManager();
        /** @var Query $query */
        $query = $em->createQuery(
            'SELECT l, COUNT(v.list) AS HIDDEN num_views
            FROM ListsIOListBundle:LIOListView v, ListsIOListBundle:LIOList l
            WHERE v.list = l
            GROUP BY v.list
            ORDER BY num_views DESC'
        )->setMaxResults($limit);

        $lists = $this->serialize($query->getResult());
        return $this->render('ListsIOListBundle:API:lists.json.twig', array('lists' => $lists));
    }

    public function viewNearbyListsAction(Request $request) {
        $format = $request->getRequestFormat();
        $locString = $request->query->get('locString');
        $limit = $request->query->get('limit', 10);
        $offset = $request->query->get('offset', 0);
        $profileUserId = $request->query->get('profileUserId', false);

        // If the profileUserId param is set, we're loading nearby lists belonging to a particular user.
        if ($profileUserId) {
            $user = $this->loadEntityFromId('ListsIOUserBundle:User', $profileUserId);
            $emptyMessage = 'Ah snap, ' . $user->getUsername() . " doesn't have any lists about " . $locString . ".";
            $query = "SELECT l
            FROM ListsIOListBundle:LIOList l
            WHERE l.locString = ?1
            AND l.title IS NOT NULL
            AND l.title <> ''
            AND l.user = ?2
            ORDER BY l.updatedAt DESC";
        } else {
            $user = $this->getUser();
            $createURL = $this->generateUrl('lists_io_edit_new_list');
            $emptyMessage = 'Ah snap, no lists found in ' . $locString . ', <a href="' . $createURL . '">create the first</a>!';
            $query = "SELECT l
            FROM ListsIOListBundle:LIOList l
            WHERE l.locString = ?1
            AND l.title IS NOT NULL
            AND l.title <> ''
            AND l.user != ?2
            ORDER BY l.updatedAt DESC";
        }

        $em = $this->getDoctrine()->getManager();
        $lists = $em->createQuery($query)->setParameter(1, $locString)
            ->setParameter(2, $user)
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getResult();

        $this->get('logger')->error($this->serialize($lists));

        if ($format == 'json') {
            $lists = $this->serialize($lists);
        }
        return $this->render('ListsIOListBundle:API:lists.' . $format . '.twig', array('lists' => $lists, 'emptyMessage' => $emptyMessage));
    }

}
