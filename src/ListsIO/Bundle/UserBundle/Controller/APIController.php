<?php
/**
 * Created by PhpStorm.
 * User: jesserosato
 * Date: 6/8/14
 * Time: 5:41 PM
 */

namespace ListsIO\Bundle\UserBundle\Controller;

use ListsIO\Bundle\UserBundle\Entity\Follow;
use ListsIO\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class APIController extends Controller {

    /**
     * View user by ID.
     * @param Request $request
     * @param $userId
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function viewUserAction(Request $request, $userId)
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
     * Create a new Follow for the current logged-in user.
     * @param Request $request
     * @return Response
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function newFollowAction(Request $request)
    {
        $follower = $this->getUser();
        $followedId = $request->request->get('followedId');
        $followed = $this->loadEntityFromId('ListsIO\Bundle\UserBundle\Entity\User', $followedId);
        if (empty($followed)) {
            throw new HttpException(404, "Could not find user to be followed by ID: " . htmlspecialchars($followedId));
        }
        $follow = $this->loadOneEntityBy(
            'ListsIO\Bundle\UserBundle\Entity\Follow',
            array('followed' => $followed, 'follower' => $follower)
        );
        if ( ! empty($follow)) {
            throw new HttpException(409, $follower->getUsername()." already follows ".$followed->getUsername().".");
        }
        $follow = new Follow();
        $follower->addFollow($follow);
        $followed->addFollowedBy($follow);
        $this->saveEntities(array($follower, $followed));
        return $this->jsonResponse($follow, Response::HTTP_CREATED);
    }

    /**
     * @param Request $request
     * @param $followId
     * @return Response
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function removeFollowAction(Request $request, $followId)
    {
        $follower = $this->getUser();
        if ( $followId ) {
            $follow = $this->loadEntityFromId('ListsIO\Bundle\UserBundle\Entity\Follow', $followId);
        } else {
            $followedId = $request->request->get('followedId');
            $followed = $this->loadEntityFromId('ListsIO\Bundle\UserBundle\Entity\User', $followedId);
            $follow = $this->loadOneEntityBy('ListsIO\Bundle\UserBundle\Entity\Follow', array('followed' => $followed));
        }
        if (empty($follow)) {
            throw new HttpException(404, "Could not find follow.");
        }
        if ($follower->getId() != $follow->getFollower()->getId()) {
            throw new HttpException(403, "You have to be logged in to unfollow.");
        }
        $this->removeEntity($follow);
        return $this->jsonResponse(null, Response::HTTP_NO_CONTENT);
    }

} 