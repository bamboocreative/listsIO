<?php

namespace ListsIO\Bundle\UserBundle\Security\Core\Exception;

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use HWI\Bundle\OAuthBundle\Security\Core\Exception\OAuthAwareExceptionInterface;

class AccountValidationException extends BadCredentialsException implements OAuthAwareExceptionInterface
{
    /**
     * @var string
     */
    protected $resourceOwnerName;
    /**
     * @var OAuthToken
     */
    protected $token;

    /**
     * {@inheritdoc}
     */
    public function getAccessToken()
    {
        return $this->token->getAccessToken();
    }

    /**
     * @return array
     */
    public function getRawToken()
    {
        return $this->token->getRawToken();
    }

    /**
     * {@inheritdoc}
     */
    public function getRefreshToken()
    {
        return $this->token->getRefreshToken();
    }

    /**
     * {@inheritdoc}
     */
    public function getExpiresIn()
    {
        return $this->token->getExpiresIn();
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenSecret()
    {
        return $this->token->getTokenSecret();
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceOwnerName()
    {
        return $this->resourceOwnerName;
    }

    /**
     * {@inheritdoc}
     */
    public function setResourceOwnerName($resourceOwnerName)
    {
        $this->resourceOwnerName = $resourceOwnerName;
    }

    /**
     * {@inheritdoc}
     */
    public function setToken(TokenInterface $token)
    {
        $this->token = $token;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(array(
            $this->resourceOwnerName,
            $this->token,
            parent::serialize(),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($str)
    {
        list(
            $this->resourceOwnerName,
            $this->token,
            $parentData
        ) = unserialize($str);
        parent::unserialize($parentData);
    }
}
