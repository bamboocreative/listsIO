<?php

namespace ListsIO\Bundle\UserBundle\Controller;

use ListsIO\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PageController extends Controller
{
    /**
     * View user by username.
     * @param Request $request
     * @param $username
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function viewUserAction(Request $request, $username)
    {
        $viewUser = $this->loadOneEntityBy('ListsIO\Bundle\UserBundle\Entity\User', array('username' => $username));
        if (empty($viewUser)) {
            throw new HttpException(404, "No route or username found for username: " . htmlspecialchars($username));
        }
        // Set the target path so user's can be redirected here after login (esp. when trying to follow a user).
        $this->get('session')->set('target_path', $this->get('router')->generate('lists_io_user_view_by_username', array('username' => $username)));
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
