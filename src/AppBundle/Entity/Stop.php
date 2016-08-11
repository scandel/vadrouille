<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Stop
 *
 * @ORM\Table(name="prod.Stops")
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
     * @ORM\Column(name="delta", type="smallint")
     */
    private $delta;

    /**
     * @ORM\ManyToOne(targetEntity="City")
     * @ORM\JoinColumn(name="city_id", referencedColumnName="id", nullable=false)
     */
    private $city;

    /**
     * @var string : the RV place (adress or known place, like "mairie", "porte de la Chapelle"...)
     *
     * @ORM\Column(name="place", type="string", length=255, nullable=true)
     */
    private $place;

    /**
     * @ORM\Column(type="geometry", options={"geometry_type"="POINT", "srid"=4326})
     */
    private $point;

    /**
     * @var : the time interval from departure (first stop) in seconds
     *
     * @ORM\Column(name="time", type="integer", nullable=true)
     */
    private $time;

    /**
     * @var : the price from departure, to this stop
     *
     * @ORM\Column(name="price", type="integer", nullable=true)
     */
    private $price;

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

    /**
     * Set city
     *
     * @param  $city
     * @return Stop
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return \AppBundle\Entity\City 
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set place
     *
     * @param string $place
     * @return Stop
     */
    public function setPlace($place)
    {
        $this->place = $place;

        return $this;
    }

    /**
     * Get place
     *
     * @return string 
     */
    public function getPlace()
    {
        return $this->place;
    }
    
    /**
     * Set time
     *
     * @param \DateTime $time
     * @return Stop
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * Get time
     *
     * @return \DateTime 
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Set price
     *
     * @param integer $price
     * @return Stop
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return integer 
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set point
     *
     * @param string WKT $point : 'SRID=3785;POINT(37.4220761 -122.0845187)' ou 'POINT(37.4220761 -122.0845187)'
     * @return City
     */
    public function setPoint($point, $srid=4326)
    {
        // WKT for a point  containing a SRID
        if (preg_match('/^SRID=\d{4};POINT\([-.\d]+ [-.\d]+\)/i', $point)) {
            $this->point = $point;
        }
        // WKT for a point  with no SRID: add default SRID
        else if (preg_match('/^POINT\([-.\d]+ [-.\d]+\)/i', $point)) {
            $this->point = "SRID=$srid;" . $point;
        }
        return $this;
    }

    /**
     * Get point
     *
     * @return string (As_EWKT)
     */
    public function getPoint()
    {
        return $this->point;
    }

    /**
     * Set together lat and lng, via setCenter
     *
     * @param $lat: latitude
     * @param $lng: longitude
     * @param $srid: srid, default value 4326
     * @return $this
     */
    public function setLatLng($lat, $lng, $srid=4326)
    {
        $wkt = sprintf("POINT(%f %f)",$lng,$lat);
        return $this->setPoint($wkt,$srid);
    }

    /**
     * Get together lat and lng from wkt
     *
     * @return array
     */
    public function getLatLng()
    {
        if (preg_match('/POINT\(([-.\d]+) ([-.\d])+\)/i', $this->point, $matches)) {
            $lon = $matches[1];
            $lat = $matches[2];
            return array($lat, $lon);
        }
        else return null;
    }

    /**
     * Get lat
     *
     * @return string
     */
    public function getLat()
    {
        if (list($lat, $lng) = $this->getLatLng()) {
            return $lat;
        }
        else
            return null;
    }

    /**
     * Get lng
     *
     * @return string
     */
    public function getLng()
    {
        if (list($lat, $lng) = $this->getLatLng()) {
            return $lng;
        }
        else
            return null;
    }
    
}
