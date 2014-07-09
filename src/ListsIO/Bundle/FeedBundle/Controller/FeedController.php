<?php

namespace ListsIO\Bundle\FeedBundle\Controller;

use ListsIO\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;

class FeedController extends Controller
{
    
    public function indexAction(Request $request)
    {

        $lists = $this->feedStart();
        return $this->render('ListsIOFeedBundle:Feed:feed.html.twig', array('lists' => $lists));

    }
    
    public function nextAction(Request $request)
    {
        
        $this->requireXmlHttpRequest($request);

        $cursor = $request->query->get('cursor');
        
        //4 is the oldest list :)
        if ($cursor <= 4){
        
        	$lists = json_encode(false);
	        
        } else {
	        
	        $lists = $this->serialize($this->feedNext($cursor));

        }

        return $this->render('ListsIOFeedBundle:Feed:feedNext.json.twig', array('lists' => $lists));

    }




    public function feedStart()
    {
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
    
    
    
    public function feedNext($cursor)
    {
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
