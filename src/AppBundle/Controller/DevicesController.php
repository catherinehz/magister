<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Device;
use AppBundle\Entity\Record;
use AppBundle\Entity\Emulator;
use AppBundle\Entity\PID;

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
     * @Route("/", defaults={}, name="list_devices")
     * @Method("GET")
     */
    public function listDevicesAction()
    {
        // Fetch Devices
        $repository = $this->getDoctrine()->getRepository('AppBundle:Device');
        $devices = $repository->findAll();

        //Render view templates (pass Devices to it)
        $twigData = [
            'devices' => $devices,
            'page_title' => 'Перелік об\'єктів керування',
        ];
        return $this->render('devices/list_devices.twig', $twigData);
    }

    /**
     * @Route("/{id}", defaults={}, requirements={"id": "[1-9]\d*"}, name="show_device")
     * @Method("GET")
     */
    public function showDeviceAction(Device $device)
    {
        $lastRecord = $device->getRecords()->first();
        if (!$lastRecord) {
            $lastRecordData = $device->getInitialValues();
        } else {
            $lastRecordData = $lastRecord->getData();
        }
        $twigData = [
            'device' => $device,
            'lastRecordData' => $lastRecordData,
            'page_title' => $device->getTitle()
        ];
        return $this->render('devices/show_device.twig', $twigData);
    }
}
