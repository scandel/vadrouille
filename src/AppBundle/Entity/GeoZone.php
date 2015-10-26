<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * GeoZone
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class GeoZone
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
     * @var integer
     *
     * @ORM\Column(name="level", type="smallint")
     */
    private $level;

    /**
     * @var integer
     *
     * @ORM\Column(name="localId", type="integer")
     */
    private $localId;

    /**
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=255)
     */
    private $country;

    /**
     * @var string
     *
     * @ORM\Column(name="parent", type="string", length=255)
     */
    private $parent;

    /**
     * @var string
     *
     * @ORM\Column(name="adjacentZones", type="string", length=255)
     */
    private $adjacentZones;

    /**
     * @var array
     *
     * @ORM\Column(name="mainCities", type="simple_array")
     */
    private $mainCities;


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
     * Set level
     *
     * @param integer $level
     * @return GeoZone
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Get level
     *
     * @return integer 
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Set localId
     *
     * @param integer $localId
     * @return GeoZone
     */
    public function setLocalId($localId)
    {
        $this->localId = $localId;

        return $this;
    }

    /**
     * Get localId
     *
     * @return integer 
     */
    public function getLocalId()
    {
        return $this->localId;
    }

    /**
     * Set country
     *
     * @param string $country
     * @return GeoZone
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
     * Set parent
     *
     * @param string $parent
     * @return GeoZone
     */
    public function setParent($parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return string 
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set adjacentZones
     *
     * @param string $adjacentZones
     * @return GeoZone
     */
    public function setAdjacentZones($adjacentZones)
    {
        $this->adjacentZones = $adjacentZones;

        return $this;
    }

    /**
     * Get adjacentZones
     *
     * @return string 
     */
    public function getAdjacentZones()
    {
        return $this->adjacentZones;
    }

    /**
     * Set mainCities
     *
     * @param array $mainCities
     * @return GeoZone
     */
    public function setMainCities($mainCities)
    {
        $this->mainCities = $mainCities;

        return $this;
    }

    /**
     * Get mainCities
     *
     * @return array 
     */
    public function getMainCities()
    {
        return $this->mainCities;
    }
}
