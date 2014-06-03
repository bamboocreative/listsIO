<?php

namespace ListsIO\Bundle\UserBundle\Controller;

use ListsIO\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UserController extends Controller
{

    /**
     * Home action:
     * - Anonymous: /user/register
     * - Logged in: /username
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function indexAction()
    {
        // If the user's not logged in, send them to registration.
        $securityContext = $this->container->get('security.context');
        if( ! $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') ){
            return $this->redirect($this->generateUrl('fos_user_registration_register'));
        } else {
            // Otherwise, send them to their profile page.
            $user = $this->getUser();
            $username = $user->getUsername();
            return $this->redirect($this->generateUrl('lists_io_user_view_by_username', array('username' => $username)));
        }

    }

    /**
     * View user by ID.
     * @param Request $request
     * @param $userId
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function viewByIdAction(Request $request, $userId)
    {
        $format = $request->getRequestFormat();
        $viewUser = $this->loadEntityFromId('ListsIO\Bundle\UserBundle\Entity\User', $userId);
        if (empty($viewUser)) {
            throw new HttpException(404, "Could not find user by ID: " . htmlspecialchars($userId));
        }
        $viewUser = $this->serialize($viewUser, $format);
        return $this->render('ListsIOUserBundle:Profile:show.'.$format.'.twig', array('user' => $viewUser));
    }

    /**
     * View user by username.
     * @param Request $request
     * @param $username
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function viewByUsernameAction(Request $request, $username)
    {
        $viewUser = $this->loadOneEntityBy('ListsIO\Bundle\UserBundle\Entity\User', array('username' => $username));
        if (empty($viewUser)) {
            throw new HttpException(404, "No route or username found for username: " . htmlspecialchars($username));
        }
        return $this->render('ListsIOUserBundle:Profile:show.html.twig', array('user' => $viewUser));
    }


    public function completeAccountAction(Request $request)
    {
        $user = $this->getUser();
        $form = $this->createFormBuilder($user, array('validation_groups' => array('Profile')))
            ->add('email', 'email', array(
                'label' => false,
                'attr' => array('placeholder' => 'your-email@example.com')
            ))
            ->add('save', 'submit', array('label' => 'Save'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->saveEntity($user);
            return $this->redirect($this->generateUrl('lists_io_user_view_by_username', array('username' => $user->getUsername())));
        }

        return $this->render('ListsIOUserBundle:Profile:complete_account.html.twig', array('form' => $form->createView()));
    }

}
