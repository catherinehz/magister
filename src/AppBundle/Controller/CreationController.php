<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Device;
use AppBundle\Entity\Record;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("/creation")
 */
class CreationController extends Controller
{

    /**
     * @Route("/", defaults={}, name="creation_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        // Fetch Devices
        $repository = $this->getDoctrine()->getRepository('AppBundle:Device');
        $devices = $repository->findAll();

        //Render view templates (pass Devices to it)
        return $this->render('creation/creation_index.twig', ['devices' => $devices]);
    }

    /**
     * @Route("/add-device", defaults={}, name="creation_add_device")
     * @Method("GET")
     */
    public function addDeviceAction()
    {
        // Fetch Devices
        $repository = $this->getDoctrine()->getRepository('AppBundle:Device');
        $devices = $repository->findAll();

        // Create new Device
        $devicesCount = count($devices);
        //if (!$devicesCount) {
        if (true) {
            $device = new Device();
            $device->setTitle('Scruber-'.++$devicesCount);
            
            //Config (initial values for device parameters)
            $config = array(
                'CO2-in-C2H2-y1-target'=>0.04,
                'Kp'=>0.1,
                'Ki'=>0.1,
                'Kd'=>0.1,
            );
            
            $device->setConfig($config);
            
            //Save new device to DB
            $em = $this->getDoctrine()->getManager();
            $em->persist($device);
            $em->flush();
        }

        //Redirect to index
        return $this->redirectToRoute('creation_index');
    }

    /**
     * @Route("/add-records/{id}", defaults={}, requirements={"id": "[1-9]\d*"}, name="creation_add_records")
     * @Method("GET")
     */
    public function addRecordsAction(Device $device)
    {
        // Initial Values
        $deviceConfig = $device->getConfig();
        $dataArrayInitial = array(
            'C2H2-Fg'=>100,
            'NaOH-Fr'=>960, //<-- Витрата NaOH
            'CO2-in-C2H2-y0'=>0.060,
            'CO2-in-C2H2-y1'=>0.020, //<-- Вихідн. конц. CO2 в суміші C2H2
            'CO2-in-C2H2-y1-target'=>$deviceConfig['CO2-in-C2H2-y1-target'], //<--  Завдання конц. CO2 в суміші C2H2
            'CO2-in-NaOH-x0'=>0,
            'CO2-in-NaOH-x1'=>0.0028,
            'Kg'=>30.022,
            'Kr'=>3.107,
            'm'=>9.615,
            'S'=>64.056,
            'Vg'=>3.92,
            'Vr'=>2,
            'Pg'=>1100,
            'Pr'=>2130,
            'Kp'=>$deviceConfig['Kp'],
            'Ki'=>$deviceConfig['Ki'],
            'Kd'=>$deviceConfig['Kd'],
        );
        $dataArray = $dataArrayInitial;
        
        $maxDeviationC2H2Fg = 1;
        $maxDeviationNaOHFr = 3;
        $maxDeviationCO2inC2H2y1= 0.003;
        
        $lastRecord = $device->getRecords()->first();
        $currentDateTime = new \DateTime('now', new \DateTimeZone('Europe/Kiev'));
        if ($lastRecord) {
            $dateTime = $lastRecord->getCreatedAt();
            $dateTime->setTimezone(new \DateTimeZone('Europe/Kiev'));
            $dateTime->modify('+1 min');
        } else {
            $dateTime = new \DateTime('now', new \DateTimeZone('Europe/Kiev'));
            $dateTime->modify('-100 min');
        }

        $em = $this->getDoctrine()->getManager();

        // Create bunch of records
        for ($i=0; $i<100; ++$i) {
            // Create new Record
            $record = new Record();
            $record->setDevice($device);
            $record->setData($dataArray);
            $record->setCreatedAt(clone $dateTime);

            // Put record into queue
            $em->persist(clone $record);

            // Modify values (emulate life randomly)
            
            //C2H2-Fg
            $dataArray['C2H2-Fg'] = $dataArray['C2H2-Fg'] + mt_rand(-1, 1);
            $delta = $dataArray['C2H2-Fg']-$dataArrayInitial['C2H2-Fg'];
            if (abs($delta) > $maxDeviationC2H2Fg) {
                if ($delta > 0) {
                    $dataArray['C2H2-Fg'] = $dataArrayInitial['C2H2-Fg'] + $maxDeviationC2H2Fg;
                } else {
                    $dataArray['C2H2-Fg'] = $dataArrayInitial['C2H2-Fg'] - $maxDeviationC2H2Fg;
                }
            }
            
            //NaOH-Fr
            $dataArray['NaOH-Fr'] = $dataArray['NaOH-Fr'] + mt_rand(-1, 1);
            $delta = $dataArray['NaOH-Fr']-$dataArrayInitial['NaOH-Fr'];
            if (abs($delta) > $maxDeviationNaOHFr) {
                if ($delta > 0) {
                    $dataArray['NaOH-Fr'] = $dataArrayInitial['NaOH-Fr'] + $maxDeviationNaOHFr;
                } else {
                    $dataArray['NaOH-Fr'] = $dataArrayInitial['NaOH-Fr'] - $maxDeviationNaOHFr;
                }
            }
            
            //CO2-in-C2H2-y1
            $dataArray['CO2-in-C2H2-y1'] = (float)round($dataArray['CO2-in-C2H2-y1'] + (mt_rand(-3, 3) / 1000), 3);
            $delta = $dataArray['CO2-in-C2H2-y1']-$dataArrayInitial['CO2-in-C2H2-y1'];
            if (abs($delta) > $maxDeviationCO2inC2H2y1) {
                if ($delta > 0) {
                    $dataArray['CO2-in-C2H2-y1'] = 
                        (float)round($dataArrayInitial['CO2-in-C2H2-y1'] 
                                +
                        $maxDeviationCO2inC2H2y1, 3);
                } else {
                    $dataArray['CO2-in-C2H2-y1'] = (float)round($dataArrayInitial['CO2-in-C2H2-y1'] - $maxDeviationCO2inC2H2y1, 3);
                }
            }
            
            $dateTime->modify('+1 min');
            if ($dateTime > $currentDateTime) break;
            
        }

        // Perform SQL transaction
        $em->flush();

        return $this->redirectToRoute('creation_index');
    }
}




