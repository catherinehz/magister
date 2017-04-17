<?php

namespace AppBundle\Entity;

class Emulator
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

    public function __construct(Device $device)
    {
        $this->device = $device;
        $this->deviceConfig = $device->getConfig();
        $this->deviceLastRecord = $device->getRecords()->first();
    }

    public function generateRecords()
    {
        return true;

        //Дістати із бази останній показник об'єкту
        $this->deviceLastRecord = $this->device->getRecords()->first();
        $currentDateTime = new \DateTime('now', new \DateTimeZone('Europe/Kiev'));
        if ($this->deviceLastRecord) {
            $dateTime = $this->deviceLastRecord->getCreatedAt();
            $dateTime->setTimezone(new \DateTimeZone('Europe/Kiev'));
            $dateTime->modify('+1 min');
        } else {
            $dateTime = new \DateTime('now', new \DateTimeZone('Europe/Kiev'));
            $dateTime->modify('-180 min');
        }

        //Сгенерувати нові показники об'єкту
        $amountOfRecords = 0;
        $em = $this->getDoctrine()->getManager();
        while ($currentDateTime < $dateTime) {
            //Сгенерувати нові показники об'єкту за допомогою мат. моделі
            $dataArray = $this->_generateNewValues();

            //Створити новий Record об'єкт
            $record = new Record();
            $record->setDevice($this->device);
            $record->setData($dataArray);
            $record->setCreatedAt(clone $dateTime);

            //Поставити Record об'єкт у чергу
            $em->persist(clone $record);
            $amountOfRecords++;
            $dateTime->modify('+1 min');

            //Кожні 1000 єлементів - Відправити на збереження у базу даних (фактичне виконання SQL-запитів)
            if ($amountOfRecords > 1000) {
                $amountOfRecords = 0;
                $em->flush();
            }
        }

        //Відправити на збереження у базу даних (фактичне виконання SQL-запитів)
        $em->flush();
        return true;
    }


    private function _generateNewValues() {
        //Початкові показники параметрів об'єкту керування
        if ($this->deviceLastRecord) {
            $newValues = $this->deviceLastRecord->getData();
        } else {
            $newValues = $this->_getInitialValues();
        }

        //ПІД-регулятор
        $pid = new PID($this->device);
        //Емулюємо нове значення витрати NaOH за допомогою ПІД-регулятора
        $pidResult = $pid->regulateNaOH();
        $newValues['NaOH-Fr'] = $pidResult['NaOH-Fr'];
        $newValues['integralError'] = $pidResult['integralError'];
        $newValues['derivativeError'] = $pidResult['derivativeError'];
        
        //Математична модель
        $scrubber = new ScrubberModel($this->device);
        //Емулюємо нове значення конц. СО2 на виході за допомогою мат. моделі
        $newValues['CO2-in-C2H2-y1'] = $scrubber->mathModel($newValues);

        return $newValues;
    }

    private function _getInitialValues() {
        $dataArrayInitial = array(
            'C2H2-Fg'=>100,
            'NaOH-Fr'=>960, // <-- Витрата NaOH
            'CO2-in-C2H2-y0'=>0.060,
            'CO2-in-C2H2-y1'=>0.020, // <-- Вихідн. конц. CO2 в суміші C2H2
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
            
            'CO2-in-C2H2-y1-target'=>$this->deviceConfig['CO2-in-C2H2-y1-target'], //<--  Завдання конц. CO2 в суміші C2H2
            'integralError' => 0, // <-- Інтегральна похибка для ПІД регулятора
            'derivativeError' => 0, // <-- Диференційна похибка для ПІД регулятора
            'NaOH-Fr-Max' => 1500, // <-- Границя максимальної витрати NaOH
            'NaOH-Fr-Min' => 0, // <-- Границя мінімальної витрати NaOH
            'Kp'=>$this->deviceConfig['Kp'],
            'Ki'=>$this->deviceConfig['Ki'],
            'Kd'=>$this->deviceConfig['Kd'],
        );
        return $dataArrayInitial;
    }
}
