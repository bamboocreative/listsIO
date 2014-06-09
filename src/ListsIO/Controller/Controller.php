<?php
/**
 * Created by PhpStorm.
 * User: jesserosato
 * Date: 5/15/14
 * Time: 10:08 AM
 */

namespace ListsIO\Controller;

use ListsIO\Entity\OwnableInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Controller extends BaseController {

    /**
     * @param $entity
     */
    public function saveEntity($entity) {
        $em = $this->getDoctrine()
            ->getManager();
        $em->persist($entity);
        $em->flush();
    }

    /**
     * @param string $entity_name
     * @param string $id
     * @return mixed
     */
    public function loadEntityFromId($entity_name, $id)
    {
        return $this->getDoctrine()
            ->getRepository($entity_name)
            ->find($id);
    }

    /**
     * @param string $entityName
     * @param array $findBy
     * @return object
     */
    public function loadOneEntityBy($entityName, array $findBy)
    {
        return $this->getDoctrine()
            ->getRepository($entityName)
            ->findOneBy($findBy);
    }

    /**
     * @param $entity
     */
    public function removeEntity($entity)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($entity);
        $em->flush();
    }

    /**
     * @param $entity
     * @param string $format
     * @return mixed
     */
    public function serialize($entity, $format = 'json')
    {
        // HTML doesn't need serialization.
        if ($format == 'html') {
            return $entity;
        }
        $serializer = $this->get('jms_serializer');
        return $serializer->serialize($entity, $format, SerializationContext::create()->enableMaxDepthChecks());
    }

    /**
     * TODO: Use serializer, see saveListItemAction. - Jesse Rosato 6/8/14
     * @param $entity
     * @param $data
     * @return mixed
     */
    public function deserialize($entity, $data)
    {
        foreach($data as $key => $value) {
            $funct = 'set'.ucfirst($key);
            if (method_exists($entity, $funct)) {
                $entity->$funct($value);
            }
        }
        return $entity;
    }

    protected function jsonResponse($rawContent, $statusCode = 200)
    {
        $response = new Response();
        if ( ! empty($rawContent)) {
            $content = $this->serialize($rawContent);
            $response->setContent($content);
        }
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode($statusCode);
        return $response;
    }

    protected function requireAuthentication()
    {
        $securityContext = $this->container->get('security.context');
        if( ! $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') ){
            throw new HttpException(403);
        }
        $this->get('logger')->debug("USER LOGGED IN");
    }

    /**
     * @param Request $request
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     */
    protected function requireXmlHttpRequest(Request $request)
    {
        if (! $request->isXmlHttpRequest()) {
            throw new AccessDeniedHttpException('The route you are attempting to access is not available externally.');
        }
    }

    /**
     * TODO: Use Voters or ACL, see all non-idempotent functions. - Jesse Rosato 3/27/14
     * @param OwnableInterface $object
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     */
    protected function requireUserIsObjectOwner(OwnableInterface $object) {
        $this->requireAuthentication();
        if ($this->getUser()->getId() != $object->getUser()->getId()) {
            throw new AccessDeniedHttpException("You must be authenticated as the list owner to save the list.");
        }
    }

} 