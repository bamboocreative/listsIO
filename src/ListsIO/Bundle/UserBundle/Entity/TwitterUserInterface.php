<?php
/**
 * Created by PhpStorm.
 * User: jesserosato
 * Date: 2/27/14
 * Time: 12:45 AM
 */

namespace ListsIO\Bundle\UserBundle\Entity;


interface TwitterUserInterface {

    /**
     * Set the user's Twitter ID
     *
     * @param $twitterId
     */
    public function setTwitterId($twitterId);

    /**
     * Get the user's Twitter ID
     *
     * @return string
     */
    public function getTwitterId();

    /**
     * Set the user's Twitter Access Token
     *
     * @param $twitterAccessToken
     */
    public function setTwitterAccessToken($twitterAccessToken);

    /**
     * Get the user's Twitter Access Token
     *
     * @return string
     */
    public function getTwitterAccessToken();

    /**
     * Set the user's Twitter username
     *
     * @param $twitterUsername
     */
    public function setTwitterUsername($twitterUsername);

    /**
     * Get the user's Twitter username
     *
     * @return string
     */
    public function getTwitterUsername();

} 