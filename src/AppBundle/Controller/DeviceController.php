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
 * @Route("/devices")
 */
class DeviceController extends Controller
{
    /**
     * @Route("/", defaults={"_format"="html"}, name="devices_index")
     * @Method("GET")
     * @Cache(smaxage="10")
     */
    public function indexAction($_format)
    {
        /* Fetch Devices */
        $repository = $this->getDoctrine()->getRepository('AppBundle:Device');
        $devices = $repository->findAll();

        /* Create new Device */
        $devicesCount = count($devices);
        $device = new Device();
        $device->setTitle('Scruber-'.++$devicesCount);
        $device->setCreatedAt(new \DateTime());

        $em = $this->getDoctrine()->getManager();
        $em->persist($device);
        $em->flush();

        /* Fetch Devices again (with new)*/
        $devices = $repository->findAll();

        return $this->render('device/index.'.$_format.'.twig', ['devices' => $devices]);
    }

    /**
     * @Route("/{id}", defaults={"_format"="html"}, requirements={"id": "[1-9]\d*"}, name="device_page")
     * @Method("GET")
     */
    public function deviceShowAction(Device $device)
    {
        //dump($device->getRecords());
        if (!count($device->getRecords())) {
            return $this->redirectToRoute('add_records', ['id' => $device->getId()]);
        }
        
        return $this->render('device/device_show.html.twig', ['device' => $device]);
    }

    /**
     * @Route("/add-records/{id}", defaults={"_format"="html"}, requirements={"id": "[1-9]\d*"}, name="add_records")
     * @Method("GET")
     */
    public function addRecordsAction(Device $device)
    {
        /* Initial Values */
        $tempScr = 20.0;
        $flowNaOH = 5.0;
        $dataArray = array('tempScr'=>$tempScr,'flowNaOH'=>$flowNaOH);
        
        $dateTime = new \DateTime('now', new \DateTimeZone('Europe/Kiev'));
        $dateTime->modify('-100 sec');
        
        $em = $this->getDoctrine()->getManager();
        for ($i=0; $i<100; ++$i) {
            
            
            /* Create new Record */
            $record = new Record();
            $record->setDevice($device);
            $record->setContent(json_encode($dataArray));
            $record->setCreatedAt(clone $dateTime);

            /* Put record into queue */
            $em->persist(clone $record);
            
            /* Modify values */
            $tempScr = (float)round($tempScr + (mt_rand(-20, 20) / 10), 2);
            $flowNaOH = (float)round($flowNaOH + (mt_rand(-10, 10) / 10), 2);
            $dataArray = array('tempScr'=>$tempScr,'flowNaOH'=>$flowNaOH);
            $dateTime->modify('+1 sec');
        }
        
        /* Perform SQL transaction */
        $em->flush();
        
        return $this->redirectToRoute('device_page', ['id' => $device->getId()]);
    }


    /**
     * @Route("/device/{id}/add-record", name="recird_new")
     * @Method("POST")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
     // ParamConverter("device", options={"mapping": {"deviceId": "id"}})
    public function recordNewAction(Request $request, Device $device)
    {
        /** @var Record $record */
        $record = new Record ($request->getContent());
        $record->setDevice($device);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($record);
        $entityManager->flush();

        // When triggering an event, you can optionally pass some information.
        // For simple applications, use the GenericEvent object provided by Symfony
        // to pass some PHP variables. For more complex applications, define your
        // own event object classes.
        // See http://symfony.com/doc/current/components/event_dispatcher/generic_event.html
        $event = new GenericEvent($record);

        // When an event is dispatched, Symfony notifies it to all the listeners
        // and subscribers registered to it. Listeners can modify the information
        // passed in the event and they can even modify the execution flow, so
        // there's no guarantee that the rest of this controller will be executed.
        // See http://symfony.com/doc/current/components/event_dispatcher.html
        $this->get('event_dispatcher')->dispatch(Events::RECORD_CREATED, $event);

        return $this->redirectToRoute('device', ['id' => $device->getId()]);
    }
}
