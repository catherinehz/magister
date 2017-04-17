<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Device;
use AppBundle\Entity\Record;
use AppBundle\Entity\PID;
use AppBundle\Entity\Emulator;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiController extends Controller
{
    /**
     * @Route("/api", name="api_index")
     */
    public function indexAction(Request $request)
    {
        $methodsAvailable = array(
            'methodsAvailable' => array (
                'getDeviceRecords',
                'setDeviceConfig',
                'calculateDevicePid',
            )
        );
        return new Response(json_encode($methodsAvailable));
    }

    /**
     * @Route("/api/getDeviceRecords/{id}/{page}/{recordsPerPage}", defaults={"page" = 0, "recordsPerPage" = 100}, requirements={"id": "[1-9]\d*", "page": "[0-9]\d*", "recordsPerPage": "[1-9]\d*" }, name="api_get_device_records")
     */
    public function getDeviceRecords(Device $device, $page, $recordsPerPage)
    {
        //Collect items to display
        $recordsTotalCount = $device->getRecords()->count();
        $maxPage = ceil($recordsTotalCount/$recordsPerPage)-1;
        if ($page>$maxPage) $page = $maxPage;
        $records = $device->getRecords()->slice($page*$recordsPerPage,$recordsPerPage);

        //Create array for JSON response
        $recordsArray = array();
        foreach($records as $record) {
            $recordsArray[] = array(
                'id' => $record->getId(),
                'data' => $record->getData(),
                'createdAt' => $record->getCreatedAt(),
            );
        }

        //Create JSON response
        $response = array(
            'records' => $recordsArray,
            'maxPage' => $maxPage,
            'currentPage' => $page
        );

        //Send OK response and DATA
        return new Response(json_encode($response));
    }

    /**
     * @Route("/api/getDeviceConfig/{id}", defaults={}, requirements={"id": "[1-9]\d*"}, name="api_get_device_config")
     */
    public function getDeviceConfig(Device $device)
    {
        //Collect items to display
        $deviceConfigJson = $device->getConfigJson();
        
        //Send OK response and DATA
        return new Response($deviceConfigJson);
    }

    /**
     * @Route("/api/getDeviceLastRecord/{id}", defaults={}, requirements={"id": "[1-9]\d*"}, name="api_get_device_last_record")
     */
    public function getDeviceLastRecord(Device $device)
    {
        //Get ilast record (using ->first() as it uses DESC ordering)
        $entity = $device->getRecords()->first();

        //Create JSON response
        $json = $entity->toJson();

        //Send JSON answer
        return new Response($json);
    }

    /**
     * @Route("/api/updateDeviceConfig/{id}", defaults={}, requirements={"id": "[1-9]\d*"}, name="api_update_device_config")
     * @Method({"POST"})
     */
    public function updateDeviceConfig(Device $device, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        
        //Чи треба записати нове завдання CO2-in-C2H2-y1
        $CO2inC2H2y1Target = $request->get('CO2-in-C2H2-y1-target');
        if ($CO2inC2H2y1Target) {
            $CO2inC2H2y1Target = (float)$CO2inC2H2y1Target;
            $deviceConfig = $device->getConfig(); //вертає массив з конфігураційними параметрами
            $deviceConfig['CO2-in-C2H2-y1-target'] = $CO2inC2H2y1Target;
            $device->setConfig($deviceConfig);
            
            $em->persist($device); //Постанова на чергу для запису
        }
        
        //Чи треба записати нове значення Kp
        $Kp = $request->get('Kp');
        if ($Kp) {
            $Kp = (float)$Kp;
            $deviceConfig = $device->getConfig(); //вертає массив з конфігураційними параметрами
            $deviceConfig['Kp'] = $Kp;
            $device->setConfig($deviceConfig);
            
            $em->persist($device); //Постанова на чергу для запису
        }
        
        //Чи треба записати нове значення Ki
        $Ki = $request->get('Ki');
        if ($Ki) {
            $Ki = (float)$Ki;
            $deviceConfig = $device->getConfig(); //вертає массив з конфігураційними параметрами
            $deviceConfig['Ki'] = $Ki;
            $device->setConfig($deviceConfig);
            
            $em->persist($device); //Постанова на чергу для запису
        }
        
        //Чи треба записати нове значення Kd
        $Kd = $request->get('Kd');
        if ($Kd) {
            $Kd = (float)$Kd;
            $deviceConfig = $device->getConfig(); //вертає массив з конфігураційними параметрами
            $deviceConfig['Kd'] = $Kd;
            $device->setConfig($deviceConfig);
            
            $em->persist($device); //Постанова на чергу для запису
        }
        
        $em->flush(); //Запис у базу даних

        //Send OK response and DATA
        return new Response('OK', 200);
    }

    /**
     * @Route("/api/generatePidChart/{id}", defaults={}, requirements={"id": "[1-9]\d*"}, name="api_generate_pid_chart")
     * @Method({"GET","POST"})
     */
    public function generatePidChart(Device $device, Request $request)
    {
        $Kp = (float)$request->get('Kp');
        $Ki = (float)$request->get('Ki');
        $Kd = (float)$request->get('Kd');
        
        $pid = new PID($device);
        $chartData = $pid->generatePidChart($Kp, $Ki, $Kd);
        
        //Send OK response and DATA
        return new Response($chartData, 200);
    }

    /**
     * @Route("/api/sync/{id}", defaults={}, requirements={"id": "[1-9]\d*"}, name="api_sync")
     * @Method({"GET","POST"})
     */
    public function sync(Device $device)
    {
        //Принять данные от объекта
        //Записать их в базу (табл. Records)
        
        //Достать свежие данные конфигурации табл. Device (задание для СО2, Kp, Ki, Kd) = $deviceConfig
        
        //Отправить $deviceConfig объхекту управления
        return new Response($deviceConfig, 200);
    }

    /**
     * @Route("/api/emulateDevice/{id}", defaults={}, requirements={"id": "[1-9]\d*"}, name="api_emulate_device")
     * @Method({"GET","POST"})
     */
    public function emulateDevice(Device $device)
    {
        $emulator = new Emulator($device);
        $emulator->generateRecords();
        
        return new Response("OK", 200);
    }
}
