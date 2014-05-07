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
use ListsIO\Bundle\ListBundle\Entity\LIOListView;

class LoadListViewData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $list = $this->getReference('list1');
        $listView = new LIOListView();
        $listView->setList($list);
        $listView->setUser($this->getReference('user2'));
        $manager->persist($listView);
        $listView = new LIOListView();
        $listView->setList($list);
        $listView->setUser($this->getReference('user3'));
        $manager->persist($listView);
        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 4; // the order in which fixtures will be loaded
    }
}