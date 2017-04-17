<?php

namespace AppBundle\Entity;

class ScrubberModel
{

    /**
     * @var Device
     */
    private $device;

    public function __construct(Device $device)
    {
        $this->device = $device;
    }

    private function transferFunction() {
        //Device data = завдання об'єкту
        $c = $this->device->getConfig();
        
        //Last record data = поточний стан об'єкту
        $lastRecord = $device->getRecords()->first();
        $rd = $lastRecord->getData();

        //Kx1y1
        $numerator = $rd['S']*$rd['Kg']*$rd['m'];
        $denominator = (2*$rd['Fg']) + ($rd['S']*$rd['Kg']);
        $Kx1y1 = $numerator/$denominator;

        //KFrx1
        $numerator = 2*($rd['CO2-in-NaOH-x1']-$rd['CO2-in-NaOH-x0']);
        $denominator = (2*$rd['Fg']) + ($rd['S']*$rd['Kg']);
        $KFrx1 =  $numerator/$denominator;

        //Ky1x1
        $numerator = $rd['S']*$rd['Kr'];
        $denominator = $rd['m']*((2*$rd['Fg']) + ($rd['S']*$rd['Kg']));
        $Ky1x1 =  $numerator/$denominator;
        
        //Ty1
        $numerator = $rd['Vg']*$rd['Pg'];
        $denominator = (2*$rd['Fg']) + ($rd['S']*$rd['Kg']);
        $Ty1 =  $numerator/$denominator;
        
        //Tx1
        $numerator = $rd['Vr']*$rd['Pr'];
        $denominator = (2*$rd['Fr']) + ($rd['S']*$rd['Kr']);
        $Tx1 =  $numerator/$denominator;

        //WFr
        $numerator = $Kx1y1*$KFrx1;
        $denominator = ($Kx1y1*$Ky1x1) - (($Ty1+1)*($Tx1+1));
        $WFr = $numerator/$denominator;

        return $WFr;
    }

    /**
     * 
     * @param array $newValues <- витрата NaOH встановлена PID-регулятором
     * @return float
     */
    private function mathModel($newValues) {
        //Device data = завдання об'єкту
        $c = $this->device->getConfig();
        
        //Last record data = поточний стан об'єкту
        $lastRecord = $device->getRecords()->first();
        $rd = $lastRecord->getData();

        //Витрата NaOH встановлена PID-регулятором
        $FrFromPid = (float)$FrFromPid;
        
        //Розрахунок вихідного значення конц. СО2
        //TODO: Math
        $y1Math = 0.033; // CO2 кг/м.куб.
        
        //Відносне значення (у відсотках)
        $y1Result = round(($y1Math / $c['CO2-in-C2H2-y1-target']), 5);
        
        return $y1Result; //Результуюча вихідна концентрація CO2 у відсотках
    }

    /**
     * 
     * @param array $newValues <- витрата NaOH встановлена PID-регулятором
     * @return float
     */
    private function mathModelMock($newValues) {
        //Device data = завдання об'єкту
        $c = $this->device->getConfig();
        
        //Last record data = поточний стан об'єкту
        $lastRecord = $device->getRecords()->first();
        $rd = $lastRecord->getData();

        //Витрата NaOH встановлена PID-регулятором
        $FrFromPid = (float)$FrFromPid;
        
        //Розрахунок вихідного значення конц. СО2
        //TODO: Math
        $y1Math = 0.033; // CO2 кг/м.куб.
        
        return $y1Result; //Результуюча вихідна концентрація CO2 у відсотках
    }

}
