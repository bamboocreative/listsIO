<?php
/**
 * Created by PhpStorm.
 * User: jesserosato
 * Date: 3/4/14
 * Time: 9:04 PM
 */

namespace ListsIO\Bundle\UserBundle\Model;

interface FacebookUserInterface {

    /**
     * Set the user's Facebook ID
     *
     * @param $facebookId
     */
    public function setFacebookId($facebookId);

    /**
     * Get the user's Facebook ID
     *
     * @return string
     */
    public function getFacebookId();

    /**
     * Set the user's Facebook Access Token
     *
     * @param $facebookAccessToken
     */
    public function setFacebookAccessToken($facebookAccessToken);

    /**
     * Get the user's Facebook Access Token
     *
     * @return string
     */
    public function getFacebookAccessToken();

    /**
     * Set the user's Facebook username
     *
     * @param $facebookUsername
     */
    public function setFacebookUsername($facebookUsername);

    /**
     * Get the user's Facebook username
     *
     * @return string
     */
    public function getFacebookUsername();

} 