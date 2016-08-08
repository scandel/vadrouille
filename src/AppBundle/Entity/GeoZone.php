<?php

namespace AppBundle\Entity;

use AppBundle\AppBundle;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * GeoZone
 *
 * @ORM\Table(name="static.GeoZones")
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
     * @ORM\OneToMany(targetEntity="GeoZoneName", mappedBy="geoZone")
     */
    private $names;

    /**
     * Level : 1 = Region (top level in a country), 2 = Département
     *
     * @var integer
     *
     * @ORM\Column(name="level", type="smallint")
     */
    private $level;

    /**
     * Local id of the zone ; ex 27 for Eure
     *
     * @var integer
     *
     * @ORM\Column(name="localId", type="integer")
     */
    private $localId;

    /**
     * @ORM\ManyToOne(targetEntity="Country")
     * @ORM\JoinColumn(name="country_code", referencedColumnName="code")
     */
    private $country;

    /**
     * @ORM\ManyToOne(targetEntity="GeoZone", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true)
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="GeoZone", mappedBy="parent")
     */
    private $children;

    /**
     * @ORM\ManyToMany(targetEntity="GeoZone")
     * @ORM\JoinTable(name="static.GeoZonesAdjacent",
     *      joinColumns={@ORM\JoinColumn(name="zone1", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="zone2", referencedColumnName="id")}
     *      )
     */
    private $adjacentZones;

    /**
     * A simple array of ids of the 10 or 20 (depends on level) main Cities
     * of the zone (acts like a cache).
     *
     * @ORM\Column(name="mainCities", type="simple_array", nullable=true)
     */
    private $mainCities;

    /**
     * Set Id
     * (not working when AI activated)
     *
     * @param integer $id
     * @return GeoZone
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
     * @param Country $country
     * @return GeoZone
     */
    public function setCountry(Country $country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return Country
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
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->names = new \Doctrine\Common\Collections\ArrayCollection();
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
        $this->adjacentZones = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add names
     *
     * @param \AppBundle\Entity\GeoZoneName $names
     * @return GeoZone
     */
    public function addName(\AppBundle\Entity\GeoZoneName $names)
    {
        $this->names[] = $names;

        return $this;
    }

    /**
     * Remove names
     *
     * @param \AppBundle\Entity\GeoZoneName $names
     */
    public function removeName(\AppBundle\Entity\GeoZoneName $names)
    {
        $this->names->removeElement($names);
    }

    /**
     * Get names
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getNames()
    {
        return $this->names;
    }

    /**
     * Get Name for the Zone in specified language
     *
     * @param string $language
     * @return mixed|null|string
     */
    public function getName($language = "")
    {
        $language = ($language) ? $language : 'fr';
        $namesInLanguage = array();
        foreach ($this->names as $name) {
            if ($name->getLanguage() == $language) {
                $namesInLanguage[] = $name ;
            }
        }
        if (count($namesInLanguage) > 0) {
            return $namesInLanguage[0];
        }
        else {
            return "";
        }
    }

    /**
     * Add children
     *
     * @param \AppBundle\Entity\GeoZone $children
     * @return GeoZone
     */
    public function addChild(\AppBundle\Entity\GeoZone $children)
    {
        $this->children[] = $children;

        return $this;
    }

    /**
     * Remove children
     *
     * @param \AppBundle\Entity\GeoZone $children
     */
    public function removeChild(\AppBundle\Entity\GeoZone $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getChildren()
    {
        return $this->children;
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
     * Add adjacentZones
     *
     * @param \AppBundle\Entity\GeoZone $adjacentZones
     * @return GeoZone
     */
    public function addAdjacentZone(\AppBundle\Entity\GeoZone $adjacentZones)
    {
        $this->adjacentZones[] = $adjacentZones;

        return $this;
    }

    /**
     * Remove adjacentZones
     *
     * @param \AppBundle\Entity\GeoZone $adjacentZones
     */
    public function removeAdjacentZone(\AppBundle\Entity\GeoZone $adjacentZones)
    {
        $this->adjacentZones->removeElement($adjacentZones);
    }
}
