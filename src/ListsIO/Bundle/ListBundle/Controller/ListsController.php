<?php

namespace ListsIO\Bundle\ListBundle\Controller;

use ListsIO\Bundle\ListBundle\Entity\LIOList;
use ListsIO\Bundle\ListBundle\Entity\LIOListItem;
use ListsIO\Bundle\ListBundle\Entity\LIOListView;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

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

        $list_user = $list->getUser();
        $user = $this->getUser();
        $data = array(
            'user'  => $user,
            'list_user' => $list_user,
            'list' => $list
        );

        /*
         * Save view list only for logged-in users.
         */
        if (! empty($user) && $list_user->getId() != $user->getId()) {
            $listView = new LIOListView();
            $listView->setUser($user);
            $listView->setList($list);
            $this->saveEntity($listView);
        }

        // If list does not belong to user, render view template, otherwise render edit template.
        if (empty($user) || $list_user->getId() != $user->getId()) {
            return $this->render('ListsIOListBundle:Lists:viewList.'.$format.'.twig', $data);
        }

        return $this->render('ListsIOListBundle:Lists:editList.html.twig', $data);
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
        return $this->render('ListsIOListBundle:Lists:viewList.'.$format.'.twig', array('list' => $list));
    }

    /**
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
        return $this->render('ListsIOListBundle:Lists:viewListItem.'.$format.'.twig', array('listItem' => $listItem));
    }

    /**
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

    /**
     * @param Request $request
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     */
    public function requireXmlHttpRequest(Request $request)
    {
        if (! $request->isXmlHttpRequest()) {
            throw new AccessDeniedHttpException('The route you are attempting to access is not available externally.');
        }
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
     * @param $entity
     */
    public function saveEntity($entity) {
        $em = $this->getDoctrine()
            ->getManager();
        $em->persist($entity);
        $em->flush();
    }

    /**
     * @param string $entity_name
     * @param string $id
     * @return mixed
     */
    public function loadEntityFromId($entity_name, $id)
    {
        return $this->getDoctrine()
            ->getRepository($entity_name)
            ->find($id);
    }

    /**
     * @param $entity
     */
    public function removeEntity($entity)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($entity);
        $em->flush();
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

}
