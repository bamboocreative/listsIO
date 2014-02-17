<?php

namespace ListsIO\Bundle\ListBundle\Controller;

use ListsIO\Bundle\ListBundle\Entity\LIOList;
use ListsIO\Bundle\ListBundle\Entity\LIOListItem;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as Response;

class ListsController extends Controller
{

    public function viewListAction(Request $request, $id)
    {
        $format = $request->getRequestFormat();
        $list = $this->loadEntityFromId('ListsIOListBundle:LIOList', $id);

        if (empty($list)) {
            throw $this->createNotFoundException("Unable to find list with id: " . $id . ".");
        }

        $list_user = $list->getUser();
        $user = $this->getUser();
        $data = array(
            'user'  => $user,
            'list_user' => $list_user,
            'list' => $list
        );
        // If list belongs to user, render edit template, otherwise render view template.
        if ( empty($user) || ! ($list_user->getId() === $user->getId())) {
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

    public function saveListAction(Request $request, $userId)
    {
        $this->requireXmlHttpRequest($request);
        $format = $request->getRequestFormat();
        $data = $request->request->all();
        $user = $this->loadEntityFromId('ListsIOUserBundle:User', $userId);
        if (empty($user)) {
            throw $this->createNotFoundException("Unable to load user to save list.");
        }
        $list = $this->loadEntityFromId('ListsIO\Bundle\ListBundle\Entity\LIOList', $data['id']);
        if (empty($list)) {
            throw $this->createNotFoundException("Unable to find list by ID.");
        }
        $list->setTitle($data['title']);
        $list->setSubtitle($data['subtitle']);
        $list->setImageURL($data['imageURL']);
        $list->setUser($user);
        $this->saveEntity($list);
        return $this->render('ListsIOListBundle:Lists:viewList.'.$format.'.twig', $list);
    }

    public function saveListItemAction(Request $request, $listId)
    {
        $this->requireXmlHttpRequest($request);
        $format = $request->getRequestFormat();
        $data = $request->request->all();
        $list = $this->loadEntityFromId('ListsIOListBundle:LIOList', $listId);
        if (empty($list)) {
            throw $this->createNotFoundException("Unable to load list to save list item.");
        }
        $listItem = $this->loadEntityFromId('ListsIO\Bundle\ListBundle\Entity\LIOListItem', $data['id']);
        $listItem->setTitle($data['title']);
        $listItem->setDescription($data['description']);
        $this->saveEntity($listItem);
        return $this->render('ListsIOListBundle:Lists:viewListItem.'.$format.'.twig', array('listItem' => $listItem));
    }

    public function removeListAction(Request $request, $listId)
    {
        $this->requireXmlHttpRequest($request);
        $list = $this->loadEntityFromId('ListsIO\Bundle\ListBundle\Entity\LIOList', $listId);
        if (empty($list)) {
            throw $this->createNotFoundException("Couldn't find list to remove it.");
        }
        $this->removeEntity($list);
        $response = json_encode(array('success' => TRUE));
        return new Response($response);
    }

    public function removeListItemAction(Request $request, $itemId)
    {
        $this->requireXmlHttpRequest($request);
        $listItem = $this->loadEntityFromId('ListsIO\Bundle\ListBundle\Entity\LIOListItem', $itemId);
        if (empty($listItem)) {
            throw $this->createNotFoundException("Couldn't find list item to remove it.");
        }
        $this->removeEntity($listItem);
        $response = json_encode(array('success' => TRUE));
        return new Response($response);
    }

    public function requireXmlHttpRequest(Request $request)
    {
        if (! $request->isXmlHttpRequest()) {
            throw new AccessDeniedException('The route you are attempting to access is not available externally.');
        }
    }

    public function saveEntity($entity) {
        $em = $this->getDoctrine()
            ->getManager();
        $em->persist($entity);
        $em->flush();
    }

    public function loadEntityFromId($entity_name, $id)
    {
        return $this->getDoctrine()
            ->getRepository($entity_name)
            ->find($id);
    }

    public function removeEntity($entity)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($entity);
        $em->flush();
    }

    public function newListItem($list)
    {
        $em = $this->getDoctrine()->getManager();
        $listItem = new LIOListItem();
        if (empty($list)) {
            throw new EntityNotFoundException("Unable to load list to create new list item.");
        }
        $listItem->setList($list);
        $em->persist($list);
        $em->persist($listItem);
        $em->flush();
        return $listItem;
    }

}
