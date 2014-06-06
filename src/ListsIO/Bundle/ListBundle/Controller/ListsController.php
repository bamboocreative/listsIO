<?php

namespace ListsIO\Bundle\ListBundle\Controller;

use JMS\Serializer\SerializationContext;
use ListsIO\Bundle\ListBundle\Entity\LIOList;
use ListsIO\Bundle\ListBundle\Entity\LIOListItem;
use ListsIO\Bundle\ListBundle\Entity\LIOListLike;
use ListsIO\Bundle\ListBundle\Entity\LIOListView;
use ListsIO\Bundle\UserBundle\Entity\User;
use ListsIO\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use JMS\Serializer\Serializer;
use Doctrine\ORM\EntityManager;

// TODO: REST API (INCLUDING AUTHENTICATION!)
class ListsController extends Controller
{

    public function viewListAction(Request $request, $id)
    {
        $format = $request->getRequestFormat();

        /**
         * @var $list LIOList
         */
        $list = $this->loadEntityFromId('ListsIOListBundle:LIOList', $id);

        if (empty($list)) {
            throw $this->createNotFoundException("Unable to find list.");
        }

        // Set the target path so user's can be redirected here after login (esp. when trying to like list).
        $this->get('session')->set('target_path', $this->get('router')->generate('lists_io_view_list', array('id' => $id)));

        $user = $this->getUser();

        $this->saveListView($request, $list, $user);
        $liked = $this->listIsLikedByUser($list, $user);

        // Prep template data.
        $data = array(
            'list'          => $list,
            'liked'         => $liked,
        );

        // If list does not belong to user, render view template, otherwise render edit template.
        if (empty($user) || $list->getUser()->getId() != $user->getId()) {
            // Format is always HTML for edit, only try serialization for view.
            $data['list'] = $this->serialize($list, $format);
            return $this->render('ListsIOListBundle:Lists:viewList.'.$format.'.twig', $data);
        }

        return $this->render('ListsIOListBundle:Lists:editList.html.twig', $data);
    }

    public function viewPopularListsAction(Request $request)
    {
        $limit = $request->request->get('limit');
        $limit = $limit ? $limit : 10;
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT l, COUNT(v.list) AS HIDDEN num_views
            FROM ListsIOListBundle:LIOListView v, ListsIOListBundle:LIOList l
            WHERE v.list = l
            GROUP BY v.list
            ORDER BY num_views DESC'
        )->setMaxResults($limit);

        $lists = $this->serialize($query->getResult());
        return $this->render('ListsIOListBundle:Lists:viewLists.json.twig', array('lists' => $lists));
    }

    public function newListAction()
    {
        $list = new LIOList();
        $list->setUser($this->getUser());
        $this->newListItem($list);
        return $this->redirect($this->generateUrl('lists_io_view_list', array('id' => $list->getId())));
    }

    public function newListItemAction(Request $request, $listId)
    {
        $this->requireXmlHttpRequest($request);
        $format = $request->getRequestFormat();
        $list = $this->loadEntityFromId('ListsIO\Bundle\ListBundle\Entity\LIOList', $listId);
        $listItem = $this->newListItem($list);
        $listItem = $this->serialize($listItem, $format);
        return $this->render('ListsIOListBundle:Lists:viewListItem.'.$format.'.twig', array('listItem' => $listItem));
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function saveListAction(Request $request)
    {
        $this->requireXmlHttpRequest($request);
        $format = $request->getRequestFormat();
        $data = $request->request->all();
        $user = $this->getUser();
        $list = $this->loadEntityFromId('ListsIO\Bundle\ListBundle\Entity\LIOList', $data['id']);
        if (empty($list)) {
            throw $this->createNotFoundException("Unable to find list by ID.");
        }
        // TODO: Use Voters or ACL, see all non-idempotent functions. - Jesse Rosato 3/27/14
        $this->requireUserIsListOwner($list);
        // TODO: Use serializer, see saveListItemAction. - Jesse Rosato 3/27/14
        foreach($data as $key => $value) {
            $funct = 'set'.ucfirst($key);
            if (method_exists($list, $funct)) {
                $list->$funct($value);
            }
        }
        $list->setUser($user);
        $this->saveEntity($list);
        $list = $this->serialize($list, $format);
        return $this->render('ListsIOListBundle:Lists:viewList.'.$format.'.twig', array('list' => $list));
    }

    /**
     * TODO: Use HTTP response code.
     *
     * @param Request $request
     * @param $listId
     * @return Response
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function saveListItemAction(Request $request, $listId)
    {
        $this->requireXmlHttpRequest($request);
        $format = $request->getRequestFormat();
        $data = $request->request->all();
        $this->get('logger')->error(print_r($data, true));
        $list = $this->loadEntityFromId('ListsIOListBundle:LIOList', $listId);
        if (empty($list)) {
            throw $this->createNotFoundException("Unable to load list to save list item.");
        }
        $this->requireUserIsListOwner($list);
        $listItem = $this->loadEntityFromId('ListsIO\Bundle\ListBundle\Entity\LIOListItem', $data['id']);
        if (empty($listItem)) {
            throw $this->createNotFoundException("Unable to load list item to save it.");
        }
        foreach($data as $key => $value) {
            $funct = 'set'.ucfirst($key);
            if (method_exists($listItem, $funct)) {
                $listItem->$funct($value);
            }
        }

        $this->saveEntity($listItem);
        $listItem = $this->serialize($listItem, $format);
        return $this->render('ListsIOListBundle:Lists:viewListItem.'.$format.'.twig', array('listItem' => $listItem));
    }

    /**
     * TODO: Use HTTP response code instead of 'success' response.
     *
     * @param Request $request
     * @param $listId
     * @return Response
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function removeListAction(Request $request, $listId)
    {
        $this->requireXmlHttpRequest($request);
        $list = $this->loadEntityFromId('ListsIO\Bundle\ListBundle\Entity\LIOList', $listId);
        if (empty($list)) {
            throw $this->createNotFoundException("Couldn't find list to remove it.");
        }
        $this->requireUserIsListOwner($list);
        $this->removeEntity($list);
        $response = json_encode(array('success' => TRUE, 'id' => $listId));
        return new Response($response);
    }

    /**
     * TODO: Use HTTP response code instead of 'success' response.
     *
     * @param Request $request
     * @param $itemId
     * @return Response
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function removeListItemAction(Request $request, $itemId)
    {
        $this->requireXmlHttpRequest($request);
        $listItem = $this->loadEntityFromId('ListsIO\Bundle\ListBundle\Entity\LIOListItem', $itemId);
        if (empty($listItem)) {
            throw $this->createNotFoundException("Couldn't find list item to remove it.");
        }
        $list = $listItem->getList();
        $this->requireUserIsListOwner($list);
        $list->removeListItem($listItem);
        $this->removeEntity($listItem);
        $this->saveEntity($list);
        $response = json_encode(array('success' => TRUE, 'id' => $itemId));
        return new Response($response);
    }

    public function likeListAction(Request $request)
    {
        $this->requireXmlHttpRequest($request);
        $listId = $request->get('listId');
        $list = $this->loadEntityFromId('ListsIO\Bundle\ListBundle\Entity\LIOList', $listId);
        if (empty($list)) {
            throw $this->createNotFoundException("Couldn't find list to like it.");
        }

        $user = $this->getUser();

        $repo = $this->getDoctrine()->getRepository('ListsIO\Bundle\ListBundle\Entity\LIOListLike');
        $likes = $repo->findOneBy(array('list' => $list, 'user' => $user));
        if (count($likes)) {
            throw new HttpException(409, 'You already liked this list.');
        }

        $like = new LIOListLike();
        $like->setList($list);
        $like->setUser($user);
        $this->saveEntity($like);
        $response = $this->serialize($like, $request->getRequestFormat());
        return new Response($response, 201);
    }

    /**
     * @param LIOList $list
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     */
    protected function requireUserIsListOwner(LIOList $list) {
        if ($this->getUser()->getId() != $list->getUser()->getId()) {
            throw new AccessDeniedHttpException("You must be authenticated as the list owner to save the list.");
        }
    }

    /**
     * @param LIOList $list
     * @return LIOListItem
     * @throws EntityNotFoundException
     */
    public function newListItem(LIOList $list)
    {
        $em = $this->getDoctrine()->getManager();
        $listItem = new LIOListItem();
        if (empty($list)) {
            throw new EntityNotFoundException("Unable to load list to create new list item.");
        }
        $list->addListItem($listItem);
        $em->persist($list);
        $em->flush();
        return $listItem;
    }

    /**
     * @param Request $request
     * @param LIOList $list
     * @param User $user
     */
    public function saveListView(Request $request, LIOList $list, User $user = null) {
        $listView = new LIOListView();
        $listView->setList($list);
        // Author views should not count towards list views.
        if ( ! empty($user)) {
            if ($list->getUser()->getId() != $user->getId()) {
                $listView->setUser($user);
            }
        } else {
            $listView->setAnonymousIdentifier($request->headers->get('User-Agent'), $request->getClientIp());
        }
        $this->saveEntity($listView);
    }

    /**
     * @param LIOList $list
     * @param User $user
     * @return bool
     */
    public function listIsLikedByUser(LIOList $list, User $user = null) {
        if ( ! empty($user)) {
            // Figure out whether the user has liked this list before.
            $repo = $this->getDoctrine()->getRepository('ListsIO\Bundle\ListBundle\Entity\LIOListLike');
            $listLikes = $repo->findOneBy(
                array(
                    'list'   => $list,
                    'user'   => $user
                )
            );
            return !! (count($listLikes));
        }
    }

}
