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

class LoadListData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $list = new LIOList();
        $list->setTitle("Test title");
        $user = $this->getReference('user1');
        $list->setUser($user);
        $manager->persist($list);
        $manager->flush();
        $this->addReference('list1', $list);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 3; // the order in which fixtures will be loaded
    }
}