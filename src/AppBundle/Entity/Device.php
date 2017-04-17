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
     * @ORM\OrderBy({"id": "DESC"})
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
}
