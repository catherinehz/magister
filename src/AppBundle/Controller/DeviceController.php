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

    private $_objectCalculationsDone = 0;

    /**
     * @Route("/", defaults={}, name="list_devices")
     * @Method("GET")
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

    /**
     * @Route("/{id}", defaults={}, requirements={"id": "[1-9]\d*"}, name="watch_device")
     * @Method("GET")
     */
    public function watchDeviceAction(Device $device)
    {
        if (!count($device->getRecords())) {
            return $this->redirectToRoute('add_records', ['id' => $device->getId()]);
        }

        return $this->render('device/watch_device.twig', ['device' => $device]);
    }

    /**
     * @Route("/test-chart", defaults={}, requirements={}, name="test_chart")
     * @Method("GET")
     */
    public function testChartAction()
    {
        //$W_reg2 = -0,77808*(((1+3,4p)*(1+25p))/(p(1+12p)));

        /*
        $results = [];
        $p = 1;
        for ($i = 1; $i<=100; ++$i) {
        //for ($i = 0; $i<=100; $i=$i+1) {
            //$WFrp = pow($i,2);
            //$WFrp = (-0.023)/((4.083*pow($i,2))+(4.041*$i)+0.1814);
            $WFrp = -166.25*(  (1+(0.5*$p))*(1+(2*$p))  /  $p*(1+(12*$p))   );
            $p = $p + 1;
            $results[] = array('y'=>$WFrp, 'x'=>$i);
        }*/


        $results = array();
        $results[0] = $this->_buildPRegulatorChart(0.15, 0.00, 0.00);
        $results[1] = $this->_buildPRegulatorChart(0.06, 0.02, 0.06);

        return $this->render('device/test_chartjs.twig', ['results' => $results]);
    }

    private function _buildPRegulatorChart($Kp, $Ki, $Kd, $inertia = 6, $limit = 80) {
        $results = [];
        $i = 0;
        $timeStep = 1;

        $targetValue = (float)1;
        $currentValue = (float)0;
        $currentTime = (int)0;
        $results[] = array('y'=>$currentValue, 'x'=>$currentTime);

        //First iteration
        $error = $targetValue - $currentValue;

        $pRegulator = $error * $Kp;
        $iRegulator = $error * $Ki;
        $iState = 0;
        $preError = 0;
        $dt = 6;


        while (true) {
            $currentTime = $currentTime + $timeStep;
            $error = $targetValue - $currentValue;

            if ($i % $inertia == 0 && abs($error) > 0.02) {
                //Proportional regulator effect
                $pRegulator = $error * $Kp;

                //Integral regulator effect
                $iState = ($iRegulator + ($error*$dt));
                if ($iState > 1) $iState = 1;
                if ($iState < -1) $iState = -1;
                $iRegulator = $Ki * $iState;
                
                //Integral regulator effect
                //$dState = ($dRegulator + ($error*$dt));
                $dState = ($error - $preError)/$dt;
                $dRegulator = $Kd * $dState;
                $preError = $error;
                
                //Reset object calculations counter
                $this->_objectCalculationsDone = 0;
            }

            $pid = $pRegulator + $iRegulator + $dRegulator;
            
            $currentValue = $currentValue + $pid + $this->_calculateObjectBehaviour();

            $results[] = array('y'=>$currentValue, 'x'=>$currentTime);


            ++$i;
            if ($i > $limit) break;
        }

        return $results;
    }


    private function _calculateObjectBehaviour() {
        $this->_objectCalculationsDone++;
        $result = (-0.023)/((4.083*pow($this->_objectCalculationsDone,2))+(4.041*$this->_objectCalculationsDone)+0.1814);
        return $result*0;
    }

    /**
     * @Route("/add-records/{id}", defaults={}, requirements={"id": "[1-9]\d*"}, name="add_records")
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

        /* Create bunch of records */
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

        return $this->redirectToRoute('watch_device', ['id' => $device->getId()]);
    }
}
