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
use ListsIO\Bundle\UserBundle\Entity\User;

class LoadUserData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setUsername('testuser');
        $user->setEmail('test@example.com');
        $user->setPlainPassword('test');

        $manager->persist($user);
        $manager->flush();

        $this->addReference('user1', $user);

        $user = new User();
        $user->setUsername('testuser2');
        $user->setEmail('test2@example.com');
        $user->setPlainPassword('test');

        $manager->persist($user);
        $manager->flush();

        $this->addReference('user2', $user);

        $user = new User();
        $user->setUsername('testuser3');
        $user->setEmail('test3@example.com');
        $user->setPlainPassword('test');

        $manager->persist($user);
        $manager->flush();

        $this->addReference('user3', $user);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 1; // the order in which fixtures will be loaded
    }
}