<?php
/**
 * Created by PhpStorm.
 * User: jesserosato
 * Date: 2/26/14
 * Time: 11:50 PM
 */

namespace ListsIO\Bundle\UserBundle\Controller;

use HWI\Bundle\OAuthBundle\Controller\ConnectController as BaseController;


class ConnectController extends BaseController {

    /**
     * Connects a user to a given account if the user is logged in and connect is enabled.
     *
     * @param Request $request The active request.
     * @param string  $service Name of the resource owner to connect to.
     *
     * @throws \Exception
     *
     * @return Response
     *
     * @throws NotFoundHttpException if `connect` functionality was not enabled
     * @throws AccessDeniedException if no user is authenticated
     */
    public function connectServiceAction(Request $request, $service)
    {
        $connect = $this->container->getParameter('hwi_oauth.connect');
        if (!$connect) {
            throw new NotFoundHttpException();
        }

        $hasUser = $this->container->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED');
        if (!$hasUser) {
            throw new AccessDeniedException('Cannot connect an account.');
        }

        // Get the data from the resource owner
        $resourceOwner = $this->getResourceOwnerByName($service);

        $session = $request->getSession();
        $key = $request->query->get('key', time());

        if ($resourceOwner->handles($request)) {
            $accessToken = $resourceOwner->getAccessToken(
                $request,
                $this->generate('hwi_oauth_connect_service', array('service' => $service), true)
            );

            // save in session
            $session->set('_hwi_oauth.connect_confirmation.'.$key, $accessToken);
        } else {
            $accessToken = $session->get('_hwi_oauth.connect_confirmation.'.$key);
        }

        $userInformation = $resourceOwner->getUserInformation($accessToken);

        // Show confirmation page?
        if (!$this->container->getParameter('hwi_oauth.connect.confirmation')) {
            goto show_confirmation_page;
        }

        // Handle the form
        /** @var $form FormInterface */
        $form = $this->container->get('form.factory')
            ->createBuilder('form')
            ->getForm();

        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                show_confirmation_page:

                /** @var $currentToken OAuthToken */
                $currentToken = $this->container->get('security.context')->getToken();
                $currentUser  = $currentToken->getUser();

                $this->container->get('hwi_oauth.account.connector')->connect($currentUser, $userInformation);

                if ($currentToken instanceof OAuthToken) {
                    // Update user token with new details
                    $this->authenticateUser($request, $currentUser, $service, $currentToken->getRawToken(), false);
                }

                return $this->container->get('templating')->renderResponse('HWIOAuthBundle:Connect:connect_success.html.' . $this->getTemplatingEngine(), array(
                    'userInformation' => $userInformation,
                ));
            }
        }

        return $this->container->get('templating')->renderResponse('HWIOAuthBundle:Connect:connect_confirm.html.' . $this->getTemplatingEngine(), array(
            'key'             => $key,
            'service'         => $service,
            'form'            => $form->createView(),
            'userInformation' => $userInformation,
        ));
    }

} 