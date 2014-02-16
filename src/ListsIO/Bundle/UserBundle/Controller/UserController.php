<?php

namespace ListsIO\Bundle\UserBundle\Controller;

use Doctrine\ORM\EntityNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class UserController extends Controller
{

    public function viewByIdAction($userId)
    {
        $user = $this->getUser();
        $viewUser = $this->getDoctrine()
            ->getRepository('ListsIO\Bundle\UserBundle\Entity\User')
            ->find($userId);
        if (empty($viewUser)) {
            throw new EntityNotFoundException("Could not find user by ID.");
        }
        return $this->render('ListsIOUserBundle:Profile:show.html.twig', array('view_user' => $viewUser, 'user' => $user));
    }

    public function viewByUsernameAction($username)
    {
        $user = $this->getUser();
        $viewUser = $this->getDoctrine()
            ->getRepository('ListsIO\Bundle\UserBundle\Entity\User')
            ->findOneBy(array('username' => $username));
        if (empty($viewUser)) {
            throw new EntityNotFoundException("Could not find user by username.");
        }
        return $this->render('ListsIOUserBundle:Profile:show.html.twig', array('view_user' => $viewUser, 'user' => $user));
    }
}
