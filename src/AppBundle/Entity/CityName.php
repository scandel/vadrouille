<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * CityName
 *
 * @ORM\Table(name="static.CitiesNames")
 * @ORM\Entity
 * @UniqueEntity("slug")
 */
class CityName
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
     * @ORM\ManyToOne(targetEntity="City", inversedBy="names")
     * @ORM\JoinColumn(name="city_id", referencedColumnName="id")
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="norm_name", type="string", length=255, nullable=false)
     */
    private $normName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $slugNotUnique;

    /**
     * @ORM\Column(name="slug", type="string", length=255, unique=true, nullable=true)
     */
    private $slug;

    /**
     * @var string
     *
     * @ORM\Column(name="language", type="string", length=5)
     */
    private $language = 'fr';

    /**
     * @var boolean
     *
     * @ORM\Column(name="main", type="boolean")
     */
    private $main = false;

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
     * Set name
     *
     * @param string $name
     * @return CityName
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
     * Set language
     *
     * @param string $language
     * @return CityName
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
     * Set main
     *
     * @param boolean $main
     * @return CityName
     */
    public function setMain($main)
    {
        $this->main = $main;

        return $this;
    }

    /**
     * Get main
     *
     * @return boolean 
     */
    public function isMain()
    {
        return $this->main;
    }

    /**
     * Set normName
     *
     * @param string $normName
     * @return CityName
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
     * Set city
     *
     * @param \AppBundle\Entity\City $city
     * @return CityName
     */
    public function setCity(\AppBundle\Entity\City $city = null)
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
     * Get main
     *
     * @return boolean 
     */
    public function getMain()
    {
        return $this->main;
    }

    /**
     * Set slugNotUnique
     *
     * @param string $slugNotUnique
     * @return CityName
     */
    public function setSlugNotUnique($slugNotUnique)
    {
        $this->slugNotUnique = $slugNotUnique;

        return $this;
    }

    /**
     * Get slugNotUnique
     *
     * @return string 
     */
    public function getSlugNotUnique()
    {
        return $this->slugNotUnique;
    }

    /**
     * Set slug
     *
     * @param string $slug
     * @return CityName
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string 
     */
    public function getSlug()
    {
        return $this->slug;
    }
}
