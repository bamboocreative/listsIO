<?php

namespace ListsIO\Bundle\UserBundle\Controller;

use Doctrine\ORM\EntityNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UserController extends Controller
{

    public function viewByIdAction(Request $request, $userId)
    {
        $format = $request->getRequestFormat();
        $user = $this->getUser();
        $viewUser = $this->getDoctrine()
            ->getRepository('ListsIO\Bundle\UserBundle\Entity\User')
            ->find($userId);
        if (empty($viewUser)) {
            throw new EntityNotFoundException("Could not find user by ID.");
        }
        return $this->render('ListsIOUserBundle:Profile:show.'.$format.'.twig', array('view_user' => $viewUser, 'user' => $user));
    }

    public function viewByUsernameAction(Request $request, $username)
    {
        $format = $request->getRequestFormat();
        $user = $this->getUser();
        $viewUser = $this->getDoctrine()
            ->getRepository('ListsIO\Bundle\UserBundle\Entity\User')
            ->findOneBy(array('username' => $username));
        if (empty($viewUser)) {
            throw new HttpException(404, "No route or username found.");
        }
        return $this->render('ListsIOUserBundle:Profile:show.'.$format.'.twig', array('view_user' => $viewUser, 'user' => $user));
    }
}
