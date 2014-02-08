<?php

namespace ListsIO\Bundle\ListBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class ListsController extends Controller
{
    public function indexAction()
    {
        return $this->redirect($this->generateUrl('fos_user_registration_register'));
    }

    public function viewSingleAction(Request $request, $id)
    {
        $format = $request->getRequestFormat();
        $list = $this->getDoctrine()
            ->getRepository('ListsIOListBundle:LIOList')
            ->find($id);

        if (empty($list)) {
            throw new ResourceNotFoundException("Unable to find list.");
        }

        $list_user = $list->getUser();
        $user = $this->getUser();
        $data = array(
            'user' => $user,
            'list' => $list
        );

        if ($list_user->getID() === $user->getID()) {
            return $this->render('ListsIOListBundle:Lists:editList.html.twig', $data);
        } else {
            return $this->render('ListsIOListBundle:Lists:viewList.'.$format.'.twig', $data);
        }
    }
}
