<?php

namespace ListsIO\Bundle\FeedBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;

class FeedController extends Controller
{

    
    public function indexAction(Request $request)
    {
     	     	   
        $lists = $this->listsFeed();
        
        return $this->render('ListsIOFeedBundle:Feed:feed.html.twig', array('lists' => $lists));
    }
    
    public function nextAction(Request $request)
    {
        
        $cursor = $request->query->get('cursor');
        
        $response = new JsonResponse();
        
        //4 is the oldest list :)
        if ($cursor <= 4){
        
        	$response->setData(array(
			    'lists' => false,
			));
        
        	return $response;
	        
        } else {
	        
	        $lists = $this->feedNext($cursor);
	        
	        $response->setData(array(
			    'lists' => $lists
			));
			
			return $response;
        }

    }




    public function listsFeed() {
        /**
         * @var EntityRepository $listsRepo
         */
        $listsRepo = $this->getDoctrine()
            ->getRepository('ListsIOListBundle:LIOList');
        $listsQuery = $listsRepo->createQueryBuilder('l')
        	->andWhere('l.title  !=  :empty')
        	->setParameter('empty', "")
        	->orderBy('l.id' , 'DESC')
            ->setMaxResults(21)
            ->getQuery();
        return $listsQuery->getResult();
    }
    
    
    
        public function feedNext($cursor) {
        /**
         * @var EntityRepository $listsRepo
         */
        $listsRepo = $this->getDoctrine()
            ->getRepository('ListsIOListBundle:LIOList');
        $listsQuery = $listsRepo->createQueryBuilder('l')
        	->where('l.id  <  ?1')
        	->andWhere('l.title  !=  :empty')
        	->setParameter(1, $cursor)
        	->setParameter('empty', "")
        	->orderBy('l.id' , 'DESC')
            ->setMaxResults(21)
            ->getQuery();
        return $listsQuery->getResult();
    }
}
