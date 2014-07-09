<?php

namespace ListsIO\Bundle\AppBundle\Controller;

use Doctrine\ORM\Query;
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
            return $this->homeAction();
        } else {
            // Otherwise, send them to their profile page.
            $user = $this->getUser();
            $username = $user->getUsername();
            return $this->redirect($this->generateUrl('lists_io_user_view_by_username', array('username' => $username)));
        }

    }

    public function homeAction()
    {
        $em = $this->getDoctrine()->getManager();
        /** @var Query $query */
        $query = $em->createQuery(
            'SELECT l, COUNT(v.list) AS HIDDEN num_views
            FROM ListsIOListBundle:LIOListView v, ListsIOListBundle:LIOList l
            WHERE v.list = l
            GROUP BY v.list
            ORDER BY num_views DESC'
        )->setMaxResults(20);

        $lists = $query->getResult();
        return $this->render('ListsIOAppBundle:App:home.html.twig', array('lists' => $lists));
    }
}
