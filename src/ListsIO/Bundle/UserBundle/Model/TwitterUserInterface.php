<?php
/**
 * Created by PhpStorm.
 * User: jesserosato
 * Date: 3/4/14
 * Time: 9:04 PM
 */

namespace ListsIO\Bundle\UserBundle\Model;

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