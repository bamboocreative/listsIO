<?php
/**
 * Created by PhpStorm.
 * User: jesserosato
 * Date: 2/16/14
 * Time: 11:24 PM
 */

namespace ListsIO\Bundle\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use ListsIO\Bundle\UserBundle\Entity\Follow;
use ListsIO\Bundle\UserBundle\Entity\User;

class LoadFollowData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $user1 = $this->getReference('user1');
        $user2 = $this->getReference('user2');
        $follow = new Follow();
        $follow->setFollower($user1);
        $follow->setFollowed($user2);

        $manager->persist($follow);
        $manager->flush();

        $this->addReference('follow1', $follow);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 2; // the order in which fixtures will be loaded
    }
}