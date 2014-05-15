<?php

namespace ListsIO\Bundle\SearchBundle\Controller;

use ListsIO\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityRepository;

class SearchController extends Controller
{

    /**
     * Render template for search page.
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
    	return $this->render('ListsIOSearchBundle:Search:search.html.twig');
    }

    /**
     * Search for users and lists using 'all' query param.
     * @param Request $request
     * @return JsonResponse
     */
    public function queryAction(Request $request)
    {
        //$format = $request->getRequestFormat();
        $term = urldecode($request->query->get('all'));
        // Search lists.
        $lists = $this->searchLists($term);
        // Search users
        $users = $this->searchUsers($term);
        // Manually set response content-type (as opposed to using JsonResposne) to avoid using standard json_encode.
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $data = array(
            'users' => $users,
            'lists' => $lists
        );
		$response->setContent($this->serialize($data));
		return $response;
    }

    /**
     * Find lists containing $title in their title.
     * @param $title
     * @return array
     */
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

    /**
     * Find users containing $username in their username.
     * @param $username
     * @return array
     */
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
