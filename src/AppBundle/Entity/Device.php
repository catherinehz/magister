<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="devices")
 */
class Device
{

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $config;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $mathCode;

    /**
     * @ORM\OneToMany(targetEntity="Record", mappedBy="device", fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"id": "ASC"})
     */
    private $records;

    public function __construct()
    {
        $this->records = new ArrayCollection();
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $title
     *
     * @return Device
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $config
     *
     * @return Device
     */
    public function setConfig($config)
    {
        $this->config = json_encode($config);

        return $this;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return json_decode($this->config, true);
    }

    /**
     * @return string
     */
    public function getConfigJson()
    {
        return $this->config;
    }

    /**
     * @param string $mathCode
     *
     * @return Device
     */
    public function setMathCode($mathCode)
    {
        $this->mathCode = $mathCode;

        return $this;
    }

    /**
     * Get mathCode
     *
     * @return string
     */
    public function getMathCode()
    {
        return $this->mathCode;
    }

    /**
     * Add record
     *
     * @param \AppBundle\Entity\Record $record
     *
     * @return Device
     */
    public function addRecord(\AppBundle\Entity\Record $record)
    {
        $this->records[] = $record;

        return $this;
    }

    /**
     * Remove record
     *
     * @param \AppBundle\Entity\Record $record
     */
    public function removeRecord(\AppBundle\Entity\Record $record)
    {
        $this->records->removeElement($record);
    }

    /**
     * Get records
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRecords()
    {
        return $this->records;
    }

    /**
     * Get latest Record
     *
     * @return \AppBundle\Entity\Record $record
     */
    public function getNewestRecord()
    {
        return $this->records->last();
    }

    /**
     * Get previous Record
     *
     * @return \AppBundle\Entity\Record $record
     */
    public function getPreviousRecord()
    {
        $n = count($this->records);
        $tmpVar = $this->records->slice($n-1,1);
        /*if (count($tmpVar) == 2) {
            $previousRecord = $tmpVar[1];
        } else {
            $previousRecord = null;
        }
        return $previousRecord;*/
        return $tmpVar;
    }
    
    public function getInitialValues() {
        $config = $this->getConfig();
        $dataArrayInitial = array(
            'C2H2-Fg'=>100,
            'NaOH-Fr'=>960, // <-- Витрата NaOH
            'CO2-in-C2H2-y0'=>0.080,
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

            'CO2-in-C2H2-y1-target'=>((isset($config['CO2-in-C2H2-y1-target']))?$config['CO2-in-C2H2-y1-target']:0.020), //<--  Завдання конц. CO2 в суміші C2H2
            'integralError' => 0, // <-- Інтегральна похибка для ПІД регулятора
            'derivativeError' => 0, // <-- Диференційна похибка для ПІД регулятора
            'NaOH-Fr-Max' => 1500, // <-- Границя максимальної витрати NaOH
            'NaOH-Fr-Min' => 0, // <-- Границя мінімальної витрати NaOH
            'Kp'=>((isset($config['Kp']))?$config['Kp']:0.01),
            'Ki'=>((isset($config['Ki']))?$config['Ki']:0.01),
            'Kd'=>((isset($config['Kd']))?$config['Kd']:0.01),
        );
        return $dataArrayInitial;
    }
}
