<?php

namespace ListsIO\Bundle\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class UserController extends Controller
{

    protected $doctrine;

    public function viewAction($userId)
    {
        $user = $this->loadEntityFromId('ListsIO\Bundle\UserBundle\Entity\User', $userId);
        return $this->render('ListsIOUserBundle:Profile:show.html.twig', array('user' => $user));
    }

    public function loadEntityFromId($entity_name, $id)
    {
        $this->_initDoctrine();
        return $this->doctrine
            ->getRepository($entity_name)
            ->find($id);
    }

    protected function _initDoctrine()
    {
        if (empty($this->doctrine)) {
            $this->doctrine = $this->getDoctrine();
        }
    }
}
