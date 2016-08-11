<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * City
 *
 * @ORM\Table(name="static.Cities")
 * @ORM\Entity
 */
class City
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
     * @ORM\OneToMany(targetEntity="CityName", mappedBy="city", cascade={"persist", "remove"})
     */
    private $names;

    /**
     * @ORM\ManyToOne(targetEntity="Country")
     * @ORM\JoinColumn(name="country_code", referencedColumnName="code")
     */
    private $country;

    /**
     * Zone of level 1 = Region (in France)
     *
     * @ORM\ManyToOne(targetEntity="GeoZone")
     * @ORM\JoinColumn(name="zone1_id", referencedColumnName="id")
     */
    private $zone1;

    /**
     * Zone of level 2 = Departement (in France)
     *
     * @ORM\ManyToOne(targetEntity="GeoZone")
     * @ORM\JoinColumn(name="zone2_id", referencedColumnName="id")
     */
    private $zone2;

    /**
     * @var string
     *
     * @ORM\Column(name="postCode", type="string", length=10)
     */
    private $postCode;

    /**
     * @ORM\Column(type="geometry", options={"geometry_type"="POINT", "srid"=4326})
     */
    private $center;

    /**
     * @var integer
     *
     * @ORM\Column(name="note", type="integer")
     */
    private $note;

    /**
     * Set Id
     * (not working when AI activated)
     *
     * @param integer $id
     * @return City
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
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
     * Set country
     *
     * @param string $country
     * @return City
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return string 
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set postCode
     *
     * @param string $postCode
     * @return City
     */
    public function setPostCode($postCode)
    {
        $this->postCode = $postCode;

        return $this;
    }

    /**
     * Get postCode
     *
     * @return string 
     */
    public function getPostCode()
    {
        return $this->postCode;
    }

    /**
     * Set center
     *
     * @param string WKT $center : 'SRID=3785;POINT(37.4220761 -122.0845187)' ou 'POINT(37.4220761 -122.0845187)'
     * @return City
     */
    public function setCenter($center, $srid=4326)
    {
        // WKT for a point  containing a SRID
        if (preg_match('/^SRID=\d{4};POINT\([-.\d]+ [-.\d]+\)/i', $center)) {
            $this->center = $center;
        }
        // WKT for a point  with no SRID: add default SRID
        else if (preg_match('/^POINT\([-.\d]+ [-.\d]+\)/i', $center)) {
            $this->center = "SRID=$srid;" . $center;
        }
        return $this;
    }

    /**
     * Get center
     *
     * @return string (As_EWKT)
     */
    public function getCenter()
    {
        return $this->center;
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
        return $this->setCenter($wkt,$srid);
    }

    /**
     * Get together lat and lng from wkt
     *
     * @return array
     */
    public function getLatLng()
    {
        if (preg_match('/POINT\(([-.\d]+) ([-.\d])+\)/i', $this->center, $matches)) {
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

    /**
     * Set note
     *
     * @param integer $note
     * @return City
     */
    public function setNote($note)
    {
        $this->note = $note;

        return $this;
    }

    /**
     * Get note
     *
     * @return integer 
     */
    public function getNote()
    {
        return $this->note;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->names = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set names
     *
     * @param string $names
     * @return City
     */
    public function setNames($names)
    {
        $this->names = $names;

        return $this;
    }

    /**
     * Get names
     *
     * @return string
     */
    public function getNames()
    {
        return $this->names;
    }

    /**
     * Add names
     *
     * @param \AppBundle\Entity\CityName $names
     * @return City
     */
    public function addName(\AppBundle\Entity\CityName $name)
    {
        $this->names[] = $name;

        return $this;
    }

    /**
     * Remove names
     *
     * @param \AppBundle\Entity\CityName $names
     */
    public function removeName(\AppBundle\Entity\CityName $name)
    {
        $this->names->removeElement($name);
    }

    /**
     * Returns the main name (entity CityName) of the city (if it has sevral ones),
     * for given language.
     * If language is not specified, returns main name or any name in any language existing.
     *
     * @param string $language
     * @return mixed|null|string
     */
    public function getMainName($language = "")
    {
        $namesInLanguage = array();
        $mainName = "";
        foreach ($this->names as $name) {
            $namesInLanguage[$name->getLanguage()][] = $name ;
            if ($name->isMain()) {
                $mainName = $name;
            }
        }
        if (!$language && $mainName) {
            return $mainName;
        }
        else if ($language && isset($namesInLanguage[$language]) && count($namesInLanguage[$language]) > 0){
            return $namesInLanguage[$language][0];
        }
        else if (count($this->names) > 0) {
            return $this->names->first();
        }
        else {
            return '';
        }
    }

    /**
     * Returns the main name (string) of the city (if it has sevral ones),
     * for given language.
     * If language is not specified, returns main name or any name in any language existing.
     *
     * @param string $language
     * @return string
     */
    public function getName($language = "")
    {
        $mainName = $this->getMainName($language);
        if ($mainName) {
            return $mainName->getName();
        }
        else {
            return "";
        }
    }

     /**
     * Returns the main name slug (string) of the city (if it has sevral ones),
     * for given language.
     * If language is not specified, returns main name slug or any name slug in any language existing.
     *
     * @param string $language
     * @return string
     */
    public function getSlug($language = "")
    {
        $mainName = $this->getMainName($language);
        if ($mainName) {
            return $mainName->getSlug();
        }
        else {
            return "";
        }
    }

    /**
     * Set zone1
     *
     * @param \AppBundle\Entity\GeoZone $zone1
     * @return City
     */
    public function setZone1(\AppBundle\Entity\GeoZone $zone1 = null)
    {
        $this->zone1 = $zone1;

        return $this;
    }

    /**
     * Get zone1
     *
     * @return \AppBundle\Entity\GeoZone 
     */
    public function getZone1()
    {
        return $this->zone1;
    }

    /**
     * Set zone2
     *
     * @param \AppBundle\Entity\GeoZone $zone2
     * @return City
     */
    public function setZone2(\AppBundle\Entity\GeoZone $zone2 = null)
    {
        $this->zone2 = $zone2;

        return $this;
    }

    /**
     * Get zone2
     *
     * @return \AppBundle\Entity\GeoZone 
     */
    public function getZone2()
    {
        return $this->zone2;
    }

}
