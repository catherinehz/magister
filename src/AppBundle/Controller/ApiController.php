<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Record;
use AppBundle\Entity\Device;
use AppBundle\Events;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/api")
 */
class ApiController extends Controller
{
    /**
     * @Route("/", defaults={}, name="api_index")
     * @Method("POST")
     */
    public function indexAction()
    {
        /* Fetch Devices */
        $repository = $this->getDoctrine()->getRepository('AppBundle:Device');
        $devices = $repository->findAll();

        /* Create new Device 
        $devicesCount = count($devices);
        $device = new Device();
        $device->setTitle('Scruber-'.++$devicesCount);
        $device->setCreatedAt(new \DateTime());

        $em = $this->getDoctrine()->getManager();
        $em->persist($device);
        $em->flush();

        /* Fetch Devices again (with new)*/
        $devices = $repository->findAll();

        return $this->render('device/index.twig', ['devices' => $devices]);
    }
}
