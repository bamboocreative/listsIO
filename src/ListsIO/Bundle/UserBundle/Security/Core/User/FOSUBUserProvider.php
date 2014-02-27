<?php
/**
 * Created by PhpStorm.
 * User: jesserosato
 * Date: 2/26/14
 * Time: 2:36 PM
 */

namespace ListsIO\Bundle\UserBundle\Security\Core\User;

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider as BaseClass;
use Symfony\Component\Security\Core\User\UserInterface;

class FOSUBUserProvider extends BaseClass {

    /**
     * {@inheritDoc}
     */
    public function connect(UserInterface $user, UserResponseInterface $response)
    {
        $property = $this->getProperty($response);
        $username = $response->getUsername();

        // On connect, get the access token and user ID.
        $service = $response->getResourceOwner()->getName();

        $setter = 'set'.ucfirst($service);
        $setterId = $setter.'Id';
        $setterToken = $setter.'AccessToken';

        // "Disconnect" previously connected users.
        $previousUser = $this->userManager->findUserBy(array($property => $username));
        if (null != $previousUser) {
            $previousUser->$setterId(null);
            $previousUser->$setterToken(null);
            $this->userManager->updateUser($previousUser);
        }

        $user->$setterId($username);
        $user->setterToken($response->getAccessToken());

        $this->userManager->updateUser($user);
    }

    /**
     * {@inheritDoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $username = $response->getUsername();
        $user = $this->userManager->findUserBy(array($this->getProperty($response) => $username));

        // Check for new user
        if (null === $user) {
            $data = array(
              'service' => $response->getResourceOwner()->getName(),
              'username' => $username,
              'accessToken' => $response->getAccessToken()
            );
            // Forward user to social registration page.
            $this->forward('ListsIOUserBundle:User:socialRegister', $data);
        }

        //if user exists - go with the HWIOAuth way
        $user = parent::loadUserByOAuthUserResponse($response);

        $serviceName = $response->getResourceOwner()->getName();
        $setter = 'set' . ucfirst($serviceName) . 'AccessToken';

        //update access token
        $user->$setter($response->getAccessToken());

        return $user;
    }
} 