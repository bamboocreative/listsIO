<?php

namespace ListsIO\Bundle\UserBundle\Controller;

use Doctrine\ORM\EntityNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UserController extends Controller
{

    public function indexAction()
    {
        // If the user's not logged in, send them to registration.
        $securityContext = $this->container->get('security.context');
        if( ! $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') ){
            $url = $this->generateUrl('fos_user_registration_register');
        } else {
            // Otherwise, send them to their profile page.
            $url = $this->generateUrl('lists_io_user_view_by_username',  array('username' => $this->getUser()->getUsername()));
        }
        return $this->redirect($url);
    }

    public function viewByIdAction(Request $request, $userId)
    {
        $format = $request->getRequestFormat();
        $user = $this->getUser();
        $viewUser = $this->getDoctrine()
            ->getRepository('ListsIO\Bundle\UserBundle\Entity\User')
            ->find($userId);
        if (empty($viewUser)) {
            throw new HttpException(404, "Could not find user by ID: " . htmlspecialchars($userId));
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
            throw new HttpException(404, "No route or username found for username: " . htmlspecialchars($username));
        }

        return $this->render('ListsIOUserBundle:Profile:show.'.$format.'.twig', array('view_user' => $viewUser, 'user' => $user));
    }

    public function registerTwitterUser(Request $request)
    {
        $user = $this->getUser();
        if ( ! $user->getEnabled() ) {
            return $this->render('ListsIOUserBundle:Registration:social.html.twig', array('user' => $user, 'enabled' => "NOPE!"));
        } else {
            return $this->render('ListsIOUserBundle:Registration:social.html.twig', array('user' => $user, 'enabled' => "YEP!"));
        }
        /*
        $form = $this->createForm($user)
            ->add('email', 'text')
            ->add('password', 'password')
            ->add('confirm_password', 'password');

        $form->handleRequest($request);

        $userManager = $this->get('fos_user.user_manager');
        $user = $userManager->createUser();
        $user->setUsername($login);
        $user->setPlainPassword($pass);
        $userManager->updateUser($user);
        */
    }

}
