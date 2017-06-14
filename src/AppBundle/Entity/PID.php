<?php

namespace AppBundle\Entity;

class PID
{
    /**
     * @var Device object
     */
    private $device;

    /**
     * @var array
     */
    private $deviceConfig;

    /**
     * @var Array
     */
    private $lastRecordData;

    /**
     * @var Array
     */
    private $previousRecordData;

    /**
     * Час інтегрування
     * @var float
     */
    private $_dt = 1.2;

    public function __construct(Device $device)
    {
        $this->device = $device;

        $this->deviceConfig = $device->getConfig();

        $this->lastRecord = $device->getNewestRecord();
        $this->lastRecordData = $this->lastRecord->getData();

        //$this->previousRecordData = $device->getPreviousRecord()->getData();
        $this->previousRecordData = $this->lastRecordData;
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
        $currentCO2 = $this->lastRecordData['CO2-in-C2H2-y1']; //Поточне СО2

        //ПІД-коефіцієнти
        $Kp = $this->deviceConfig['Kp'];
        $Ki = $this->deviceConfig['Ki'];
        $Kd = $this->deviceConfig['Kd'];

        //Похибка
        $error = $currentCO2 - $targetCO2;

        // --- Пропорційна складова
        $pRegulator = $error * $Kp;

        // --- Інтегральна складова
        $integralError = $this->lastRecordData['integralError'];
        $currentIntegralError = ($integralError + ($error * $this->_dt));
        $iRegulator = $Ki * $currentIntegralError;

        // --- Диференційна складова
        $currentDerivativeError = ($error - $this->previousRecordData['derivativeError']) / $this->_dt;
        $dRegulator = $Kd * $currentDerivativeError;

        //Результуючий кофіцієнт ПІД-регулятора
        $pidResult = $pRegulator + $iRegulator + $dRegulator;

        //Витрата NaOH після застосування ПІД-регулятора
        $pidNaOHFr = $this->lastRecordData['NaOH-Fr'] + ($pidResult*$this->lastRecordData['NaOH-Fr']);
        if ($pidNaOHFr > $this->deviceConfig['NaOH-Fr-Max']) {
            $pidNaOHFr = $this->deviceConfig['NaOH-Fr-Max'];
            $currentIntegralError = $integralError;
            $currentDerivativeError = $this->previousRecordData['derivativeError'];
        } elseif($pidNaOHFr < $this->deviceConfig['NaOH-Fr-Min']) {
            $pidNaOHFr = $this->deviceConfig['NaOH-Fr-Min'];
            $currentIntegralError = $integralError;
            $currentDerivativeError = $this->previousRecordData['derivativeError'];
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
        if ($Kp === null) $Kp = $this->deviceConfig['Kp'];
        if ($Ki === null) $Ki = $this->deviceConfig['Ki'];
        if ($Kd === null) $Kd = $this->deviceConfig['Kd'];

        $chartData = $this->_buildPRegulatorChart($Kp, $Ki, $Kd);

        return json_encode($chartData);
    }

    public function buildChartsNew($Kp, $Ki, $Kd, $limit = 1500) {
        $records = [];
        $records[] = $this->lastRecordData;
        for ($i=0; $i<$limit; $i++) {
            $newRecord = $this->lastRecordData;
            $pidResult = $this->regulateNaOH();
            
            $newRecord['CO2-in-C2H2-y1-target'] = $this->deviceConfig['CO2-in-C2H2-y1-target'];
            $newRecord['NaOH-Fr'] = $pidResult['NaOH-Fr'];
            $newRecord['integralError'] = $pidResult['integralError'];
            $newRecord['derivativeError'] = $pidResult['derivativeError'];

            //Математична модель
            //Емулюємо нове значення конц. СО2 на виході за допомогою мат. моделі
            $newRecord['CO2-in-C2H2-y1'] = ScrubberModel::mathModelMock($newRecord['NaOH-Fr']);
            
            $records[] = $newRecord;
            $this->lastRecordData = array_merge($this->lastRecordData, $newRecord);
            $this->previousRecordData = $this->lastRecordData;
            
            if (abs($newRecord['integralError']) <= 0.0001 && abs($newRecord['derivativeError']) <= 0.0001) break;
        }
        return $records;
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

    private function _areWeStabilizedNew($last10Points, $target, $maxDeviation) {
        foreach ($last10Points as $value) {
            if (($value >= 0 && $target >= 0) || ($value <= 0 && $target <= 0)) {
                $deviation = abs(abs($value) - abs($target));
            } elseif ($value < 0 && $target > 0) {
                $deviation = abs($target - $value);
            } else {
                $deviation = abs($value - $target);
            }
            if ($deviation > $maxDeviation) return false;
        }

        return true;
    }

    private function _calculateObjectBehaviour() {
        $this->_objectCalculationsDone++;
        $result = (-0.023)/((4.083*pow($this->_objectCalculationsDone,2))+(4.041*$this->_objectCalculationsDone)+0.1814);
        return $result*0;
    }

}
