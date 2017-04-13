<?php

namespace AppBundle\Entity;

class PID
{

    /**
     * @var Device
     */
    private $device;

    public function __construct(Device $device)
    {
        $this->device = $device;
    }

    public function calculatePID()
    {
        return 0.5;
    }

    public function generatePidChart()
    {
        $deviceConfig = $this->device->getConfig();

        $chartData = $this->_buildPRegulatorChart($deviceConfig['Kp'], $deviceConfig['Ki'], $deviceConfig['Kd']);

        return json_encode($chartData);
    }


    private function _buildPRegulatorChart($Kp, $Ki, $Kd, $inertia = 6, $limit = 499) {
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

        $last10Points = array();
        $maxDeviation = 0.02;
        
        while (true) {
            $currentTime = $currentTime + $timeStep;
            $error = $targetValue - $currentValue;

            if ($i % $inertia == 0 && abs($error) > $maxDeviation) {
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
            if (count($last10Points) < 10) {
                $last10Points[] = $currentValue;
            } else {
                array_shift($last10Points);
                $last10Points[] = $currentValue;
            }


            ++$i;
            if ($i > $limit) break;
            if ($this->_areWeStabilized($last10Points, $maxDeviation)) break;
        }
        
        return $results;
    }
    
    private function _areWeStabilized($last10Points, $maxDeviation) {
        foreach ($last10Points as $value) {
            if (abs($value-1) > $maxDeviation) return false;
        }

        return true;
    }

    private function _calculateObjectBehaviour() {
        $this->_objectCalculationsDone++;
        $result = (-0.023)/((4.083*pow($this->_objectCalculationsDone,2))+(4.041*$this->_objectCalculationsDone)+0.1814);
        return $result*0;
    }
    
    private function mathModel() {
        $config = $this->getConfig;
        $Kx1y1 = $config["S"];
        $numerator = $Kx1y1*$KFrx1;
        $denominator = ($Kx1y1*$Ky1x1) - (($Ty1+1)*($Tx1+1));
        $WFr = $numerator/$denominator;
        
        return $WFr;
    }

}
