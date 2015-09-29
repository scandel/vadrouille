<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Stop
 *
 * @ORM\Table(name="Stops")
 * @ORM\Entity
 */
class Stop
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Trip", inversedBy="stops")
     * @ORM\JoinColumn(name="trip_id", referencedColumnName="id")
     */
    private $trip;

    /**
     * @var integer
     *
     * @ORM\Column(name="delta", type="smallint")
     */
    private $delta = 1;

    /**
     * @var string
     *
     * @ORM\Column(name="city_name", type="string", length=255)
     */
    private $cityName;


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
     * Set delta
     *
     * @param integer $delta
     * @return Stop
     */
    public function setDelta($delta)
    {
        $this->delta = $delta;

        return $this;
    }

    /**
     * Get delta
     *
     * @return integer 
     */
    public function getDelta()
    {
        return $this->delta;
    }

    /**
     * Set cityName
     *
     * @param string $cityName
     * @return Stop
     */
    public function setCityName($cityName)
    {
        $this->cityName = $cityName;

        return $this;
    }

    /**
     * Get cityName
     *
     * @return string 
     */
    public function getCityName()
    {
        return $this->cityName;
    }

    /**
     * Set trip
     *
     * @param \AppBundle\Entity\Trip $trip
     * @return Stop
     */
    public function setTrip(\AppBundle\Entity\Trip $trip = null)
    {
        $this->trip = $trip;

        return $this;
    }

    /**
     * Get trip
     *
     * @return \AppBundle\Entity\Trip 
     */
    public function getTrip()
    {
        return $this->trip;
    }
}
