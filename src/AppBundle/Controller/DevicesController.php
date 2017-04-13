<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Device;
use AppBundle\Entity\Record;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * @Route("/devices")
 */
class DevicesController extends Controller
{
    /**
     * @Route("/", defaults={}, name="browse_devices")
     * @Method("GET")
     */
    public function browseDevicesAction()
    {
        // Fetch Devices
        $repository = $this->getDoctrine()->getRepository('AppBundle:Device');
        $devices = $repository->findAll();

        //Render view templates (pass Devices to it)
        return $this->render('devices/browse_devices.twig', ['devices' => $devices]);
    }

    /**
     * @Route("/{id}", defaults={}, requirements={"id": "[1-9]\d*"}, name="show_device")
     * @Method("GET")
     */
    public function showDeviceAction(Device $device)
    {
        $lastRecord = $device->getRecords()->first();
        return $this->render('devices/show_device.twig', ['device' => $device, 'lastRecord' => $lastRecord]);
    }
}
