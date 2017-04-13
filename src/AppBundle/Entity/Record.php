<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="records")
 */
class Record
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
     * @ORM\Column(type="text")
     */
    private $data;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var Device
     *
     * @ORM\ManyToOne(targetEntity="Device", inversedBy="records")
     * @ORM\JoinColumn(name="device_id", referencedColumnName="id")
     */
    private $device;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set data
     *
     * @param string $data
     *
     * @return Record
     */
    public function setData($data)
    {
        $this->data = json_encode($data);

        return $this;
    }

    /**
     * Get data
     *
     * @return string
     */
    public function getData()
    {
        return json_decode($this->data, true);
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Record
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set device
     *
     * @param \AppBundle\Entity\Device $device
     *
     * @return Record
     */
    public function setDevice(\AppBundle\Entity\Device $device = null)
    {
        $this->device = $device;

        return $this;
    }

    /**
     * Get device
     *
     * @return \AppBundle\Entity\Device
     */
    public function getDevice()
    {
        return $this->device;
    }

    /**
     * Represent self as JSON
     *
     * @return JSON
     */
    public function toJson()
    {
        $array = array(
            'id' => $this->getId(),
            'data' => $this->getData(),
            'createdAt' => $this->getCreatedAt(),
            'device' => $this->getDevice()->getId(),
        );
        
        $json = json_encode($array);
        return $json;
    }
}
