<?php

namespace ListsIO\Bundle\SearchBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;

class SearchController extends Controller
{

	public function indexAction(Request $request)
    {
    	  return $this->render('ListsIOSearchBundle:Search:search.html.twig');
    }
    
    public function findAction(Request $request)
    {
        //$format = $request->getRequestFormat();
        $term = urldecode($request->query->get('all'));
        // Search lists.
        $lists = $this->searchLists($term);
        // Search users
        $users = $this->searchUsers($term);
        //return $this->render('ListsIOSearchBundle:Search:results.'.$format.'.twig', array('term' => $term, 'users' => $users, 'lists' => $lists));
        $response = new JsonResponse();
		$response->setData(array(
		    'users' => $users,
		    'lists' => $lists
		));
		return $response;
    }

    public function searchLists($title) {
        /**
         * @var EntityRepository $listsRepo
         */
        $listsRepo = $this->getDoctrine()
            ->getRepository('ListsIOListBundle:LIOList');
        $listsQuery = $listsRepo->createQueryBuilder('l')
            ->where('l.title LIKE :title')
            ->setParameter('title', "%$title%")
            ->getQuery();
        return $listsQuery->getResult();
    }

    public function searchUsers($username) {
        /**
         * @var EntityRepository $userRepo
         */
        $userRepo = $this->getDoctrine()
            ->getRepository('ListsIOUserBundle:User');
        $userQuery = $userRepo->createQueryBuilder('u')
            ->where('u.username LIKE :username')
            ->setParameter('username', "%$username%")
            ->getQuery();
        return $userQuery->getResult();
    }
}
