<?php

namespace AppBundle\Entity;

class PID
{

    /**
     * @var Device
     */
    private $device;

    /**
     * @var array
     */
    private $deviceConfig;

    /**
     * @var Record
     */
    private $deviceLastRecord;

    /**
     * @var Record
     */
    private $devicePreviousRecord;

    /**
     * Час інтегрування
     * @var float
     */
    private $_dt = 1.5;

    public function __construct(Device $device)
    {
        $this->device = $device;

        $this->deviceConfig = $device->getConfig();

        $this->deviceLastRecord = $device->getRecords()->first();

        $tmpVar = $device->getRecords()->slice(1,1);
        if (count($tmpVar)) {
            $this->devicePreviousRecord = $tmpVar[0];
        }
    }

    /* ------ Розрахунки ПІД-регулятора ------ */

    /**
     *
     * @return float
     */
    public function regulateNaOH()
    {
        //Вхідні параметри для розрахунків
        $targetCO2 = $this->deviceConfig['CO2-in-C2H2-y1-target']; //Завдання СО"
        $currentCO2 = $this->deviceLastRecord['CO2-in-C2H2-y1']; //Поточне СО2

        //ПІД-коефіцієнти
        $Kp = $this->deviceConfig['Kp'];
        $Ki = $this->deviceConfig['Ki'];
        $Kd = $this->deviceConfig['Kd'];

        //Похибка
        $error = $targetCO2 - $currentCO2;

        // --- Пропорційна складова
        $pRegulator = $error * $Kp;

        // --- Інтегральна складова
        $integralError = $this->deviceLastRecord['integralError'];
        $currentIntegralError = ($integralError + ($error * $this->_dt));
        $iRegulator = $Ki * $currentIntegralError;

        // --- Диференційна складова
        if ($this->devicePreviousRecord) {
            $previousRecordData = $this->devicePreviousRecord->getData();
            $previousDerivativeError = $previousRecordData['derivativeError'];
        } else {
            $previousDerivativeError = 0;
        }
        $currentDerivativeError = ($error - $previousDerivativeError) / $this->_dt;
        $dRegulator = $Kd * $currentDerivativeError;

        //Результуючий кофіцієнт ПІД-регулятора
        $pidResult = $pRegulator + $iRegulator + $dRegulator;

        //Витрата NaOH після застосування ПІД-регулятора
        $pidNaOHFr = $this->deviceLastRecord['NaOH-Fr'] + ($pidResult*$this->deviceLastRecord['NaOH-Fr']);
        if ($pidNaOHFr > $this->deviceConfig['NaOH-Fr-Max']) {
            $pidNaOHFr = $this->deviceConfig['NaOH-Fr-Max'];
            $currentIntegralError = $integralError;
            $currentDerivativeError = $previousDerivativeError;
        } elseif($pidNaOHFr < $this->deviceConfig['NaOH-Fr-Min']) {
            $pidNaOHFr = $this->deviceConfig['NaOH-Fr-Min'];
            $currentIntegralError = $integralError;
            $currentDerivativeError = $previousDerivativeError;
        }
        
        $resultArray = [
            'NaOH-Fr' => $pidNaOHFr,
            'integralError' => $currentIntegralError,
            'derivativeError' => $currentDerivativeError,
        ];
        return $resultArray;
    }

    /* ------ Для налаштування ПІД-регулятора ------ */

    /**
     *
     * @param float $Kp
     * @param float $Ki
     * @param float $Kd
     * @return JSON
     */
    public function generatePidChart($Kp, $Ki, $Kd)
    {
        $deviceConfig = $this->device->getConfig();
        if ($Kp === null) $Kp = $deviceConfig['Kp'];
        if ($Ki === null) $Ki = $deviceConfig['Ki'];
        if ($Kd === null) $Kd = $deviceConfig['Kd'];

        $chartData = $this->_buildPRegulatorChart($Kp, $Ki, $Kd);

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
        $this->_dt = 6;

        $last10Points = array();
        $maxDeviation = 0.02;

        $scrubberModel = new ScrubberModel($this->device);

        while (true) {
            $currentTime = $currentTime + $timeStep;
            $error = $targetValue - $currentValue;

            if ($i % $inertia == 0 && abs($error) > $maxDeviation) {
                //Proportional regulator effect
                $pRegulator = $error * $Kp;

                //Integral regulator effect
                $iState = ($iRegulator + ($error*$this->_dt));
                if ($iState > 1) $iState = 1;
                if ($iState < -1) $iState = -1;
                $iRegulator = $Ki * $iState;

                //Integral regulator effect
                //$dState = ($dRegulator + ($error*$this->_dt));
                $dState = ($error - $preError)/$this->_dt;
                $dRegulator = $Kd * $dState;
                $preError = $error;

                //Reset object calculations counter
                $this->_objectCalculationsDone = 0;
            }

            $pid = $pRegulator + $iRegulator + $dRegulator; //0.01

            $currentValue = $currentValue + $pid + $this->_calculateObjectBehaviour();
            //$FrFromPid = ($pid*$lastRecord['NaOH-Fr'])+$lastRecord['NaOH-Fr'];
            //$currentValue = $scrubberModel->mathModel($FrFromPid);

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

}
