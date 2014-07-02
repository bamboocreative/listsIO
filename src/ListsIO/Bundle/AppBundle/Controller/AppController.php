<?php

namespace ListsIO\Bundle\AppBundle\Controller;

use ListsIO\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;

class AppController extends Controller
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
}
