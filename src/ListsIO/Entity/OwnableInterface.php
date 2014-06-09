<?php
/**
 * Created by PhpStorm.
 * User: jesserosato
 * Date: 6/7/14
 * Time: 3:45 PM
 */

namespace ListsIO\Entity;

use ListsIO\Bundle\UserBundle\Entity\User;

interface OwnableInterface {

    /**
     * @return User
     */
    public function getUser();

    /**
     * @return User
     */
    public function setUser(User $user);

} 