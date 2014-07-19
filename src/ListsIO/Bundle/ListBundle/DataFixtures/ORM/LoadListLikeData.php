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
use ListsIO\Bundle\ListBundle\Entity\LIOListLike;

class LoadListLikeData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $list = $this->getReference('list1');
        $listLike = new LIOListLike();
        $listLike->setList($list);
        $listLike->setUser($this->getReference('user2'));
        $manager->persist($listLike);
        $listLike = new LIOListLike();
        $listLike->setList($list);
        $listLike->setUser($this->getReference('user3'));
        $manager->persist($listLike);
        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 6; // the order in which fixtures will be loaded
    }
}