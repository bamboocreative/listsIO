<?php
/**
 * Created by PhpStorm.
 * User: jesserosato
 * Date: 2/16/14
 * Time: 11:24 PM
 */

namespace ListsIO\Bundle\ListBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use ListsIO\Bundle\ListBundle\Entity\LIOList;
use ListsIO\Bundle\ListBundle\Entity\LIOListItem;

class LoadListData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $list = new LIOList();
        $manager->persist($list);
        $listItem = new LIOListItem();
        $manager->persist($listItem);
        $list->addListItem($listItem);
        $user = $this->getReference('user1');
        $list->setUser($user);
        $manager->persist($list);
        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 2; // the order in which fixtures will be loaded
    }
}