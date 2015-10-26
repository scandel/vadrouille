<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * GeoZoneName
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class GeoZoneName
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
     * @var string
     *
     * @ORM\Column(name="geoZone", type="string", length=255)
     */
    private $geoZone;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="normName", type="string", length=255)
     */
    private $normName;

    /**
     * @var string
     *
     * @ORM\Column(name="language", type="string", length=5)
     */
    private $language;

    /**
     * @var array
     *
     * @ORM\Column(name="articles", type="simple_array")
     */
    private $articles;


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
     * Set geoZone
     *
     * @param string $geoZone
     * @return GeoZoneName
     */
    public function setGeoZone($geoZone)
    {
        $this->geoZone = $geoZone;

        return $this;
    }

    /**
     * Get geoZone
     *
     * @return string 
     */
    public function getGeoZone()
    {
        return $this->geoZone;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return GeoZoneName
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set normName
     *
     * @param string $normName
     * @return GeoZoneName
     */
    public function setNormName($normName)
    {
        $this->normName = $normName;

        return $this;
    }

    /**
     * Get normName
     *
     * @return string 
     */
    public function getNormName()
    {
        return $this->normName;
    }

    /**
     * Set language
     *
     * @param string $language
     * @return GeoZoneName
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get language
     *
     * @return string 
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set articles
     *
     * @param array $articles
     * @return GeoZoneName
     */
    public function setArticles($articles)
    {
        $this->articles = $articles;

        return $this;
    }

    /**
     * Get articles
     *
     * @return array 
     */
    public function getArticles()
    {
        return $this->articles;
    }
}
