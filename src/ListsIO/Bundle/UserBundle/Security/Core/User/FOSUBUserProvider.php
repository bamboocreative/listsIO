<?php
/*
 * Created by PhpStorm.
 * User: jesserosato
 * Date: 2/26/14
 * Time: 2:36 PM
 */
namespace ListsIO\Bundle\UserBundle\Security\Core\User;

use FOS\UserBundle\Model\UserManagerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider as BaseClass;
use Symfony\Component\Security\Core\User\UserInterface;
use ListsIO\Bundle\UserBundle\Entity\TwitterUserInterface;

class FOSUBUserProvider extends BaseClass {

    protected $logger;

    public function __construct(UserManagerInterface $userManager, array $properties, $logger)
    {
        parent::__construct($userManager, $properties);
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function connect(UserInterface $user, UserResponseInterface $response)
    {
        $serviceName = ucfirst($response->getResourceOwner()->getName());
        $setter = 'set'.$serviceName;
        $idSetter = $setter.'Id';
        $idGetter = 'get'.$serviceName.'Id';
        $userLoader = 'load'.$serviceName.'UserByOAuthResponse';
        $tokenSetter = $setter.'AccessToken';

        // "Disconnect" previously connected users.
        $previousUser = $this->$userLoader($response);
        if (null != $previousUser) {
            $previousUser->$idSetter(null);
            $previousUser->$tokenSetter(null);
            $this->userManager->updateUser($previousUser);
        }

        $user->$idSetter($this->$idGetter($response));
        $user->$tokenSetter($response->getAccessToken());

        $this->userManager->updateUser($user);
    }

    /**
     * {@inheritDoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $serviceName = ucfirst($response->getResourceOwner()->getName());
        $userLoader = 'load'.$serviceName.'UserByOAuthResponse';
        $dataBinder = 'bind'.$serviceName.'UserByOAuthResponse';
        $tokenSetter = 'set'.$serviceName.'AccessToken';

        $user = $this->$userLoader($response);

        // Check for new user
        if (null === $user) {
            $user = $this->userManager->createUser();
            $this->$dataBinder($response, $user);
            // Update access token
            $user->$tokenSetter($response->getAccessToken());
            $this->userManager->updateUser($user);
            return $user;
        }

        // If user exists - go with the HWIOAuth way
        $user = parent::loadUserByOAuthUserResponse($response);

        // Update access token
        $user->$tokenSetter($response->getAccessToken());

        return $user;
    }

    /**
     * Bind Twitter User by OAuth response data.
     * @param UserResponseInterface $response
     * @param TwitterUserInterface $user
     */
    protected function bindTwitterUserByOAuthResponse(UserResponseInterface $response, TwitterUserInterface $user)
    {
        $data = $response->getResponse();
        $username = $data['screen_name'];
        $id = $this->getTwitterIdByOAuthResponse($response);
        $user->setTwitterId($id);
        $user->setTwitterUsername($username);
        $user->setUsername($username);
        $user->setEmail("");
        $user->setPassword($id);
    }

    /**
     * Load user by Twitter ID by OAUTH response.
     * @param UserResponseInterface $response
     * @return \FOS\UserBundle\Model\UserInterface
     */
    protected function loadTwitterUserByOAuthResponse(UserResponseInterface $response)
    {
        $id = $this->getTwitterIdByOAuthResponse($response);
        return $this->userManager->findUserBy(array('twitterId' => $id));
    }

    /**
     * Retrieve Twitter ID by OAUTH response.
     * @param UserResponseInterface $response
     * @return null
     */
    protected function getTwitterIdByOAuthResponse(UserResponseInterface $response)
    {
        $data = $response->getResponse();
        return empty($data['id']) ? null : $data['id'];
    }
}