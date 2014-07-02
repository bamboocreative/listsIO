<?php
/**
 * Created by PhpStorm.
 * User: jesserosato
 * Date: 6/7/14
 * Time: 3:17 PM
 */

namespace ListsIO\Bundle\ListBundle\Controller;

use ListsIO\Bundle\ListBundle\Entity\LIOList;
use ListsIO\Bundle\ListBundle\Controller\Controller as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Query;
use Symfony\Component\HttpFoundation\Response;

class PageController extends BaseController {

    public function newListAction(Request $request)
    {
        $list = $this->newList();
        $url = $this->generateUrl('lists_io_edit_list', array('id' => $list->getId()));
        return $this->redirect($url, Response::HTTP_SEE_OTHER);
    }

    public function editListAction(Request $request, $id)
    {
        $list = $this->loadList($id);
        $this->requireUserIsObjectOwner($list);
        return $this->render('ListsIOListBundle:Page:editList.html.twig', array('list' => $list));
    }

} 