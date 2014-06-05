<?php
/*
 * Created by PhpStorm.
 * User: jesserosato
 * Date: 2/26/14
 * Time: 2:36 PM
 */
namespace ListsIO\Bundle\UserBundle\Security\Core\User;

use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\Validator\Validator;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider as BaseClass;
use Symfony\Component\Security\Core\User\UserInterface;
use ListsIO\Bundle\UserBundle\Model\TwitterUserInterface;
use ListsIO\Bundle\UserBundle\Model\FacebookUserInterface;
use ListsIO\Bundle\UserBundle\Security\Core\Exception\AccountValidationException;
use Symfony\Component\Translation\Translator;

class FOSUBUserProvider extends BaseClass {

    /**
     * @var Validator
     */
    protected $validator;

    /**
     * @var Translator
     */
    protected $translator;

    protected $logger;

    /**
     * Constructor
     * @param UserManagerInterface $userManager
     * @param array $properties
     * @param Validator $validator
     * @param Translator $translator
     */
    public function __construct(UserManagerInterface $userManager,
                                array $properties,
                                Validator $validator,
                                Translator $translator,
                                $logger
    )
    {
        parent::__construct($userManager, $properties);
        $this->validator = $validator;
        $this->translator = $translator;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function connect(UserInterface $user, UserResponseInterface $response) {
        $serviceName = ucfirst($response->getResourceOwner()->getName());
        $setter = 'set'.$serviceName;
        $idSetter = $setter.'Id';
        $tokenSetter = $setter.'AccessToken';
        $usernameSetter = $setter.'Username';
        $pictureSetter = $setter.'Picture';

        // "Disconnect" previously connected users.
        $previousUser = $this->loadServiceUserByOAuthResponse($response);
        if (null != $previousUser) {
            $previousUser->$idSetter(null);
            $previousUser->$tokenSetter(null);
            $this->userManager->updateUser($previousUser);
        }

        $this->$pictureSetter($response, $user);
        $user->$idSetter($this->getServiceIdByOAuthResponse($response));
        $user->$usernameSetter($response->getUsername());
        $user->$tokenSetter($response->getAccessToken());
        $this->userManager->updateUser($user);
    }

    /**
     * {@inheritDoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $serviceName = ucfirst($response->getResourceOwner()->getName());
        $tokenSetter = 'set'.$serviceName.'AccessToken';
        $pictureSetter = 'set'.$serviceName.'Picture';
        $user = $this->loadServiceUserByOAuthResponse($response);
        $this->$pictureSetter($response, $user, true);

        // Check for new user
        if (null === $user) {
            $user = $this->createServiceUserByOAuthResponse($response);
        } else {
            // If user exists - go with the HWIOAuth way
            $user = parent::loadUserByOAuthUserResponse($response);
        }

        // Update access token
        $user->$tokenSetter($response->getAccessToken());

        $this->userManager->updateUser($user);
        return $user;
    }

    /**
     * @param UserResponseInterface $response
     * @return \FOS\UserBundle\Model\UserInterface|Response
     * @throws AccountValidationException
     */
    protected function createServiceUserByOAuthResponse(UserResponseInterface $response)
    {
        $serviceName = ucfirst($response->getResourceOwner()->getName());
        $user = $this->userManager->createUser();
        $dataBinder = 'bind'.$serviceName.'UserByOAuthResponse';
        $this->$dataBinder($response, $user);

        $errors = $this->validator->validate($user);

        if (count($errors) > 0) {
            $errorMsgs = array();
            foreach ($errors as $error) {
                $errorMsgs[] = $this->translator->trans($error->getMessage(), array("%resource_owner%" => $serviceName));
            }

           throw new AccountValidationException(implode("\n", $errorMsgs));
        }

        return $user;
    }

    protected function setTwitterPicture(UserResponseInterface $response, $user, $override = false) {
        $data = $response->getResponse();
        $notDefaultPicture = empty($data['default_profile_image']);
        $this->setPicture($response, $user, $notDefaultPicture);
    }

    protected function setFacebookPicture(UserResponseInterface $response, $user, $override = false) {
        $data = $response->getResponse();
        $notDefaultPicture = empty($data['picture']['data']['is_silhouette']);
        $this->setPicture($response, $user, $notDefaultPicture, $override);
    }

    /**
     * Set the user picture from the service response.
     * - If the returned picture is not the default picture,
     * - And if override is set to true or the user does not have a profile pic set.
     * @param UserResponseInterface $response
     * @param $user
     * @param $notDefaultPicture
     * @param $override
     */
    protected function setPicture(UserResponseInterface $response, $user, $notDefaultPicture, $override = false) {
        if ( $notDefaultPicture ) {
            if ($override || preg_match('/gravatar/', $user->getProfilePicURL())) {
                // Use the original size Twitter pic.
                $profilePicURL = str_replace('_normal', '', $response->getProfilePicture());
                $user->setProfilePicURL($profilePicURL);
            }
        }
    }

    /**
     * Bind Twitter User by OAuth response data.
     * @param UserResponseInterface $response
     * @param TwitterUserInterface $user
     */
    protected function bindTwitterUserByOAuthResponse(UserResponseInterface $response, TwitterUserInterface $user)
    {
        $data = $response->getResponse();
        // We need at least screen name.
        $username = $data['screen_name'];
        $id = $this->getServiceIdByOAuthResponse($response);
        $user->setTwitterId($id);
        $user->setTwitterUsername($username);
        $user->setUsername($username);
        $user->setEmail("");
        $user->setPassword($id);
        $this->setTwitterPicture($response, $user);
    }

    /**
     * Bind Facebook user by OAuth response data.
     * @param UserResponseInterface $response
     * @param FacebookUserInterface $user
     */
    protected function bindFacebookUserByOAuthResponse(UserResponseInterface $response, FacebookUserInterface $user)
    {
        $data = $response->getResponse();
        // We gotta have at least the name field.
        $username = $data['name'];
        if ( ! empty ($data['email'])) {
            $email = $data['email'];
        }
        $id = $this->getServiceIdByOAuthResponse($response);
        $user->setFacebookId($id);
        $user->setFacebookUsername($username);
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setPassword($id);
        $this->setFacebookPicture($response, $user);
    }

    /**
     * Load user by service ID by OAUTH response.
     * @param UserResponseInterface $response
     * @return \FOS\UserBundle\Model\UserInterface
     */
    protected function loadServiceUserByOAuthResponse(UserResponseInterface $response)
    {
        $id = $this->getServiceIdByOAuthResponse($response);
        $service = $response->getResourceOwner()->getName();
        return $id ? $this->userManager->findUserBy(array($service.'Id' => $id)) : null;
    }

    /**
     * Retrieve Service ID by OAUTH response.
     * @param UserResponseInterface $response
     * @return null
     */
    protected function getServiceIdByOAuthResponse(UserResponseInterface $response)
    {
        $data = $response->getResponse();
        return empty($data['id']) ? null : $data['id'];
    }

}