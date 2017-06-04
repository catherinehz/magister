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

    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(Device $device, \Doctrine\ORM\EntityManager $em)
    {
        $this->em = $em;

        $this->device = $device;
        $this->deviceConfig = $device->getConfig();
        if (count($device->getRecords()) < 2) $this->_generateInitialRecords('-2125 min');
        $this->deviceLastRecord = $device->getNewestRecord();
    }

    public function generateRecords()
    {
        //Дістати із бази останній показник об'єкту
        $currentDateTime = new \DateTime('now', new \DateTimeZone('Europe/Kiev'));
        $dateTime = $this->deviceLastRecord->getCreatedAt();
        $dateTime->setTimezone(new \DateTimeZone('Europe/Kiev'));
        $dateTime->modify('+10 sec');

        //Сгенерувати нові показники об'єкту
        $amountOfRecords = 0;
        while ($currentDateTime > $dateTime) {
            //Сгенерувати нові показники об'єкту за допомогою мат. моделі
            $dataArray = $this->_generateNewValues();

            //Створити новий Record об'єкт
            $record = new Record();
            $record->setDevice($this->device);
            $record->setData($dataArray);
            $record->setCreatedAt(clone $dateTime);

            //Лічильник
            $amountOfRecords++;
            $dateTime->modify('+10 sec');

            $this->device->addRecord($record);
            $this->em->persist($record);

            //Поставити Record об'єкт у чергу
            /*$this->em->persist(clone $record);
            $this->device->addRecord(clone $record);

            //Кожні 1000 єлементів - Відправити на збереження у базу даних (фактичне виконання SQL-запитів)
            if ($amountOfRecords > 1000) {
                $amountOfRecords = 0;
                $this->em->flush();
            }*/

            //Stop loop
            if ($amountOfRecords > 200) break;

        }

        //Записанти Record об'єкт у базу
        $this->em->flush();

        //file_put_contents('C:\OpenServer\domains\magister\web\emulated.txt', 'Saved total: '.var_export($amountOfRecords, true).' records.'.PHP_EOL, FILE_APPEND);

        //Відправити на збереження у базу даних (фактичне виконання SQL-запитів)
        //$this->em->flush();
        return true;
    }


    private function _generateNewValues() {
        //Початкові показники параметрів об'єкту керування
        $newValues = $this->deviceLastRecord->getData();

        //ПІД-регулятор
        $pid = new PID($this->device);


        //Емулюємо нове значення витрати NaOH за допомогою ПІД-регулятора
        $pidResult = $pid->regulateNaOH();
        $newValues['CO2-in-C2H2-y1-target'] = $this->deviceConfig['CO2-in-C2H2-y1-target'];
        $newValues['NaOH-Fr'] = $pidResult['NaOH-Fr'];
        $newValues['integralError'] = $pidResult['integralError'];
        $newValues['derivativeError'] = $pidResult['derivativeError'];

        //Математична модель
        //$scrubber = new ScrubberModel($this->device);

        //Емулюємо нове значення конц. СО2 на виході за допомогою мат. моделі
        //$newValues['CO2-in-C2H2-y1'] = $scrubber->mathModelNew();
        $newValues['CO2-in-C2H2-y1'] = ScrubberModel::mathModelMock($newValues['NaOH-Fr']);

        return $newValues;
    }
    private function _generateInitialRecords($timeShift = null) {
        $dateTime = new \DateTime('now', new \DateTimeZone('Europe/Kiev'));
        if ($timeShift) $dateTime->modify($timeShift);
        else $dateTime->modify('-3 min');

        for ($i=0; $i<2; $i++) {
            //Створити новий Record об'єкт
            $record = new Record();
            $record->setDevice($this->device);
            $record->setData($this->device->getInitialValues());
            $record->setCreatedAt(clone $dateTime);

            //Записанти Record об'єкт у базу
            $this->em->persist($record);
            $this->em->flush();
            $this->device->addRecord($record);

            //Моделюємо крок інтегрування
            $dateTime->modify('+1 min');
        }


        //Оновлюємо колекцію записів пристрою Device
        $this->em->refresh($this->device);
        return true;
    }
}
