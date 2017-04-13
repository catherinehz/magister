<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Device;
use AppBundle\Entity\Record;
use AppBundle\Entity\PID;

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
     * @Route("/api/getDeviceRecords/{id}/{page}", defaults={"page" = 0}, requirements={"id": "[1-9]\d*"}, name="api_get_device_records")
     */
    public function getDeviceRecords(Device $device, $page)
    {
        //Collect items to display
        $recordsTotalCount = $device->getRecords()->count();
        $itemsPerPage = 3;
        $maxPage = ceil($recordsTotalCount/$itemsPerPage)-1;
        if ($page>$maxPage) $page = $maxPage;
        $records = $device->getRecords()->slice($page*$itemsPerPage,$itemsPerPage);

        //Create array for JSON response
        $recordsArray = array();
        foreach($records as $record) {
            $recordsArray[] = array(
                'id' => $record->getId(),
                //'data' => $record->getData(),
                'createdAt' => $record->getCreatedAt(),
            );
        }

        //Create JSON response
        $response = array(
            'items' => $recordsArray,
            'maxPage' => $maxPage,
            'currentPage' => $page
        );

        //Send OK response and DATA
        return new Response(json_encode($response));
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
     * @Route("/api/addRecordsToDevice/{id}", defaults={}, requirements={"id": "[1-9]\d*"}, name="api_add_records_to_device")
     * @Method({"POST"})
     */
    public function addRecordsToDevice(Device $device, Request $request)
    {
        //Отримуємо дані від пристрою
        
        //СО2 на виході
        $CO2inC2H2y1 = $request->get('CO2-in-C2H2-y1');
        $data['CO2-in-C2H2-y0'] = $CO2inC2H2y1; 
        $data['CO2-in-C2H2-y1'] = $CO2inC2H2y1; 
        $data['Fr'] = $CO2inC2H2y1; 
        $data['Na'] = $CO2inC2H2y1; 
        $data['CO2-in-C2H2-y1'] = $CO2inC2H2y1; 
        $data['CO2-in-C2H2-y1'] = $CO2inC2H2y1; 
        
        //Створюємо об'єкт Record
        $record = new Record();
        //Записуємо дані від пристрою у об'єкт
        $record->setData($data);
        $record->setCreatedAt();
        
        $em->persist($record); //Постанова на чергу для запису
        $em->flush(); //Запис у базу даних
        
        
        
        
        //Send response DATA
        return new Response($task, 200);
    }

    /**
     * @Route("/api/updateDeviceConfig/{id}", defaults={}, requirements={"id": "[1-9]\d*"}, name="api_update_device_config")
     * @Method({"POST"})
     */
    public function updateDeviceConfig(Device $device, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        
        //Чи треба записати нове значення CO2-in-C2H2-y1
        $CO2inC2H2y1 = $request->get('CO2-in-C2H2-y1');
        if ($CO2inC2H2y1) {
            $CO2inC2H2y1 = (float)$CO2inC2H2y1;
            $deviceConfig = $device->getConfig(); //вертає массив з конфігураційними параметрами
            $deviceConfig['CO2-in-C2H2-y1'] = $CO2inC2H2y1;
            $device->setConfig($deviceConfig);
            
            $em->persist($device); //Постанова на чергу для запису
        }
        
        //Чи треба записати нове значення CO2-in-C2H2-y1
        $Kp = $request->get('Kp');
        if ($Kp) {
            $Kp = (float)$Kp;
            $deviceConfig = $device->getConfig(); //вертає массив з конфігураційними параметрами
            $deviceConfig['Kp'] = $Kp;
            $device->setConfig($deviceConfig);
            
            $em->persist($device); //Постанова на чергу для запису
        }
        
        //Чи треба записати нове значення CO2-in-C2H2-y1
        $Ki = $request->get('Ki');
        if ($Ki) {
            $Ki = (float)$Ki;
            $deviceConfig = $device->getConfig(); //вертає массив з конфігураційними параметрами
            $deviceConfig['Ki'] = $Ki;
            $device->setConfig($deviceConfig);
            
            $em->persist($device); //Постанова на чергу для запису
        }
        
        //Чи треба записати нове значення CO2-in-C2H2-y1
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
     * @Method({"GET"})
     */
    public function generatePidChart(Device $device)
    {
        $pid = new PID($device);
        $chartData = $pid->generatePidChart();
        
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
}
