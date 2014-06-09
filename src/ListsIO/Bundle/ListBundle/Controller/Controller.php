<?php
/**
 * Created by PhpStorm.
 * User: jesserosato
 * Date: 6/7/14
 * Time: 4:05 PM
 */

namespace ListsIO\Bundle\ListBundle\Controller;

use ListsIO\Bundle\ListBundle\Entity\LIOList;
use ListsIO\Bundle\ListBundle\Entity\LIOListItem;
use ListsIO\Bundle\ListBundle\Entity\LIOListLike;
use ListsIO\Bundle\ListBundle\Entity\LIOListView;
use ListsIO\Bundle\UserBundle\Entity\User;
use ListsIO\Controller\Controller as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Controller extends BaseController {

    /**
     * @return LIOList
     */
    protected function newList()
    {
        $this->requireAuthentication();
        $list = new LIOList();
        $list->setUser($this->getUser());
        $list->addListItem(new LIOListItem());
        $this->saveEntity($list);
        return $list;
    }

    /**
     * @param LIOList $list
     * @return LIOListLike
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function newListLike(LIOList $list)
    {
        $this->requireAuthentication();
        if ($this->listIsLikedByUser($list, $this->getUser())) {
            throw new HttpException(409, 'You already liked this list.');
        }
        $like = new LIOListLike();
        $like->setList($list);
        $like->setUser($this->getUser());
        $this->saveEntity($like);
        return $like;
    }

    /**
     * @param Request $request
     * @param LIOList $list
     * @param User $user
     * @return LIOListView
     */
    public function newListView(Request $request, LIOList $list, User $user = null) {
        /** @var LIOListView $listView */
        $listView = new LIOListView();
        $listView->setList($list);
        // Author views should not count towards list views.
        if ( ! empty($user)) {
            if ($list->getUser()->getId() != $user->getId()) {
                $listView->setUser($user);
            }
        } else {
            $listView->setAnonymousIdentifier($request->headers->get('User-Agent'), $request->getClientIp());
        }
        $this->saveEntity($listView);
        return $listView;
    }

    /**
     * @param $id
     * @return LIOList
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function loadList($id)
    {
        $list = $this->loadEntityFromId('ListsIOListBundle:LIOList', $id);
        if (empty($list)) {
            throw $this->createNotFoundException("Unable to find list.");
        }
        return $list;
    }

    protected function loadListItem($itemId)
    {
        $listItem = $this->loadEntityFromId('ListsIOListBundle:LIOListItem', $itemId);
        if (empty($listItem)) {
            throw $this->createNotFoundException("Unable to load list item to save it.");
        }
        return $listItem;
    }

    /**
     * Figure out whether the user has liked this list before.
     * @param LIOList $list
     * @param User $user
     * @return bool
     */
    public function listIsLikedByUser(LIOList $list, User $user = null) {
        if (empty($user)) {
            return false;
        }
        $repo = $this->getDoctrine()->getRepository('ListsIO\Bundle\ListBundle\Entity\LIOListLike');
        $listLikes = $repo->findOneBy(
            array(
                'list'   => $list,
                'user'   => $user
            )
        );
        return !! (count($listLikes));
    }

} 