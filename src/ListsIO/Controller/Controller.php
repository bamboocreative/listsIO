<?php
/**
 * Created by PhpStorm.
 * User: jesserosato
 * Date: 5/15/14
 * Time: 10:08 AM
 */

namespace ListsIO\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use JMS\Serializer\SerializationContext;

class Controller extends BaseController {

    /**
     * @param Request $request
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     */
    public function requireXmlHttpRequest(Request $request)
    {
        if (! $request->isXmlHttpRequest()) {
            throw new AccessDeniedHttpException('The route you are attempting to access is not available externally.');
        }
    }

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
    public function serialize($entity, $format = 'json') {
        // HTML doesn't need serialization.
        if ($format == 'html') {
            return $entity;
        }
        $serializer = $this->get('jms_serializer');
        return $serializer->serialize($entity, $format, SerializationContext::create()->enableMaxDepthChecks());
    }
} 