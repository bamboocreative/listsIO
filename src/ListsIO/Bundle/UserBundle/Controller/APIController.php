<?php
/**
 * Created by PhpStorm.
 * User: jesserosato
 * Date: 6/8/14
 * Time: 5:41 PM
 */

namespace ListsIO\Bundle\UserBundle\Controller;

use ListsIO\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
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

} 