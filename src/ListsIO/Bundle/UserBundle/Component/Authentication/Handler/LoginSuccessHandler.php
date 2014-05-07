<?php
/**
 * Created by PhpStorm.
 * User: jesserosato
 * Date: 5/4/14
 * Time: 12:15 AM
 */

namespace ListsIO\Bundle\UserBundle\Component\Authentication\Handler;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Router;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{

    private $container;
    private $router;

    public function __construct(Container $container, Router $router)
    {
        $this->container = $container;
        $this->router = $router;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        /** @var $session Session */
        $session = $this->container->get('session');
        $targetPath = $session->get('target_path');
        $session->remove('target_path');
        if (empty($targetPath)) {
            $response = new RedirectResponse($this->router->generate('lists_io_home'));
        } else {
            $response = new RedirectResponse($targetPath);
        }
        return $response;
    }

} 