<?php

namespace ListsIO\Bundle\ListBundle\Controller;

use Doctrine\ORM\EntityNotFoundException;
use ListsIO\Bundle\ListBundle\Entity\LIOList;
use ListsIO\Bundle\ListBundle\Entity\LIOListItem;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Doctrine\Bundle\DoctrineBundle\Registry as Registry;

class ListsController extends Controller
{
    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var Registry
     */
    protected $doctrine;

    public function indexAction()
    {
        // If the user's not logged in, send them to registration.
        $securityContext = $this->container->get('security.context');
        if( ! $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') ){
            return $this->redirect($this->generateUrl('fos_user_registration_register'));
        }
        // Otherwise, send them to their profile page.
        return $this->render('ListsIOUserBundle:Profile:show.html.twig', array( 'user' => $this->getUser()));
    }

    public function viewListAction(Request $request, $id)
    {
        $this->_initSerializer();
        $format = $request->getRequestFormat();
        $list = $this->loadEntityFromId('ListsIOListBundle:LIOList', $id);

        if (empty($list)) {
            throw new ResourceNotFoundException("Unable to find list.");
        }

        $list_user = $list->getUser();
        $user = $this->getUser();
        $data = array(
            'user' => $user,
            'list' => ($format === 'html') ? $list : $this->serializer->serialize($list, $format)
        );
        // If list belongs to user, render edit template, otherwise render view template.
        if ($list_user->getId() === $user->getId()) {
            return $this->render('ListsIOListBundle:Lists:editList.html.twig', $data);
        } else {
            return $this->render('ListsIOListBundle:Lists:viewList.'.$format.'.twig', $data);
        }
    }

    public function newListAction()
    {
        $this->_initDoctrine();
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
        return $this->render('ListsIOListBundle:Lists:viewListItem.'.$format.'.twig',
            array('listItem' => $this->serialize($listItem, $format)));
    }

    public function saveListAction(Request $request, $userId)
    {
        $this->requireXmlHttpRequest($request);
        $format = $request->getRequestFormat();
        $data = $request->request->all();
        $user = $this->loadEntityFromId('ListsIOUserBundle:User', $userId);
        if (empty($user)) {
            throw new EntityNotFoundException("Unable to load user to save list.");
        }
        $list = $this->deserialize($data, 'ListsIO\Bundle\ListBundle\Entity\LIOList', $format);
        $list->setUser($user);
        $this->saveEntity($list);
        return $this->render('ListsIOListBundle:Lists:viewList.'.$format.'.twig',
            array('list' => $this->serialize($list, $format)));
    }

    public function saveListItemAction(Request $request, $listId)
    {
        $this->requireXmlHttpRequest($request);
        $this->_initDoctrine();
        $format = $request->getRequestFormat();
        $data = $request->request->all();
        $list = $this->loadEntityFromId('ListsIOUserBundle:LIOList', $listId);
        if (empty($list)) {
            throw new EntityNotFoundException("Unable to load list to save list item.");
        }
        $listItem = $this->deserialize($data, 'ListsIO\Bundle\ListBundle\Entity\LIOListItem', $format);
        $list->addListItem($listItem);
        $em = $this->doctrine->getManager();
        $em->persist($list);
        $em->persist($listItem);
        $em->flush();
        return $this->render('ListsIOListBundle:Lists:viewListItem.'.$format.'.twig',
            array('listItem' => $this->serialize($listItem, $format)));
    }

    public function removeListAction(Request $request, $listId)
    {
        $this->requireXmlHttpRequest($request);
        $format = $request->getRequestFormat();
        $list = $this->loadEntityFromId('ListsIO\Bundle\ListBundle\Entity\LIOList', $listId);
        $this->removeEntity($list);
        return $this->render('ListsIOListBundle:Lists:remove.'.$format.'.twig',
            array('result' => array('success' => TRUE)));
    }

    public function removeListItemAction(Request $request, $listItemId)
    {
        $this->requireXmlHttpRequest($request);
        $format = $request->getRequestFormat();
        $listItem = $this->loadEntityFromId('ListsIO\Bundle\ListBundle\Entity\LIOListItem', $listItemId);
        $this->removeEntity($listItem);
        return $this->render('ListsIOListBundle:Lists:remove.'.$format.'.twig',
            array('result' => array('success' => TRUE)));
    }

    public function requireXmlHttpRequest(Request $request)
    {
        if (! $request->isXmlHttpRequest()) {
            throw new AccessDeniedException('Lists can only be saved via XmlHttpRequest.');
        }
    }

    public function saveEntity($entity) {
        $this->_initDoctrine();
        $em = $this->doctrine
            ->getManager();
        $em->persist($entity);
        $em->flush();
    }

    public function loadEntityFromId($entity_name, $id)
    {
        $this->_initDoctrine();
        return $this->doctrine
            ->getRepository($entity_name)
            ->find($id);
    }

    public function removeEntity($entity)
    {
        $this->_initDoctrine();
        $em = $this->doctrine->getManager();
        $em->remove($entity);
        $em->flush();
    }

    public function newListItem($list)
    {
        $this->_initDoctrine();
        $em = $this->doctrine->getManager();
        $listItem = new LIOListItem();
        if (empty($list)) {
            throw new EntityNotFoundException("Unable to load list to create new list item.");
        }
        $list->addListItem($listItem);
        $em->persist($list);
        $em->persist($listItem);
        $em->flush();
        return $listItem;
    }

    public function serialize($data, $format)
    {
        $this->_initSerializer();
        return $this->serializer->serialize($data, $format);
    }

    public function deserialize($data, $entity_name, $format)
    {
        $this->_initSerializer();
        return $this->serializer->deserialize($data, $entity_name, $format);
    }

    protected function _initSerializer()
    {
        if (empty($this->serializer)) {
            $encoders = array(new JsonEncoder(), new XmlEncoder());
            $normalizer = new GetSetMethodNormalizer();
            $this->serializer = new Serializer(array($normalizer), $encoders);
        }
    }

    protected function _initDoctrine()
    {
        if (empty($this->doctrine)) {
            $this->doctrine = $this->getDoctrine();
        }
    }

}
