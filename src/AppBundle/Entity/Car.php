<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Car
 *
 * @ORM\Table()
 * @ORM\Table(name="Cars")
 * @ORM\Entity
 */
class Car
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
     * @ORM\ManyToOne(targetEntity="User", inversedBy="cars")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="CarBrand")
     * @ORM\JoinColumn(name="brand_id", referencedColumnName="id")
     */
    private $brand;

    /**
     * @ORM\ManyToOne(targetEntity="CarModel")
     * @ORM\JoinColumn(name="model_id", referencedColumnName="id")
     */
    private $model;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=20)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="color", type="string", length=20)
     */
    private $color;

    /**
     * @var string
     *
     * @ORM\Column(name="number_plate", type="string", length=20)
     */
    private $numberPlate;

    /**
     * @var string
     *
     * @ORM\Column(name="photo", type="string", length=255)
     */
    private $photo = "";

    /**
     * @var string
     *
     * @ORM\Column(name="originalPhoto", type="string", length=255)
     */
    private $originalPhoto = "";

    /**
     * @var integer
     *
     * @ORM\Column(name="places", type="smallint")
     */
    private $places = 4;

    /**
     * @var integer
     *
     * @ORM\Column(name="doors", type="smallint")
     */
    private $doors = 5;

    /**
     * @var boolean
     *
     * @ORM\Column(name="air_cond", type="boolean")
     */
    private $airCond;

    /**
     * @var boolean
     *
     * @ORM\Column(name="music", type="boolean")
     */
    private $music;


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
     * Set brand
     *
     * @param string $brand
     * @return Car
     */
    public function setBrand($brand)
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * Get brand
     *
     * @return string 
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * Set model
     *
     * @param string $model
     * @return Car
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Get model
     *
     * @return string 
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return Car
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set color
     *
     * @param string $color
     * @return Car
     */
    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Get color
     *
     * @return string 
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * Set numberPlate
     *
     * @param string $numberPlate
     * @return Car
     */
    public function setNumberPlate($numberPlate)
    {
        $this->numberPlate = $numberPlate;

        return $this;
    }

    /**
     * Get numberPlate
     *
     * @return string 
     */
    public function getNumberPlate()
    {
        return $this->numberPlate;
    }

    /**
     * Set photo
     *
     * @param string $photo
     * @return Car
     */
    public function setPhoto($photo)
    {
        $this->photo = $photo;

        return $this;
    }

    /**
     * Get photo
     *
     * @return string 
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * Set originalPhoto
     *
     * @param string $originalPhoto
     * @return Car
     */
    public function setOriginalPhoto($originalPhoto)
    {
        $this->originalPhoto = $originalPhoto;

        return $this;
    }

    /**
     * Get originalPhoto
     *
     * @return string 
     */
    public function getOriginalPhoto()
    {
        return $this->originalPhoto;
    }

    /**
     * Set places
     *
     * @param integer $places
     * @return Car
     */
    public function setPlaces($places)
    {
        $this->places = $places;

        return $this;
    }

    /**
     * Get places
     *
     * @return integer 
     */
    public function getPlaces()
    {
        return $this->places;
    }

    /**
     * Set doors
     *
     * @param integer $doors
     * @return Car
     */
    public function setDoors($doors)
    {
        $this->doors = $doors;

        return $this;
    }

    /**
     * Get doors
     *
     * @return integer 
     */
    public function getDoors()
    {
        return $this->doors;
    }

    /**
     * Set airCond
     *
     * @param boolean $airCond
     * @return Car
     */
    public function setAirCond($airCond)
    {
        $this->airCond = $airCond;

        return $this;
    }

    /**
     * Get airCond
     *
     * @return boolean 
     */
    public function getAirCond()
    {
        return $this->airCond;
    }

    /**
     * Set music
     *
     * @param boolean $music
     * @return Car
     */
    public function setMusic($music)
    {
        $this->music = $music;

        return $this;
    }

    /**
     * Get music
     *
     * @return boolean 
     */
    public function getMusic()
    {
        return $this->music;
    }

    /*=======================================================
     * Photo Related Methods
     *=======================================================*/

    public function getAbsolutePhotoPath()
    {
        return null === $this->photo ? null : $this->getUploadRootDir().'/'.$this->photo;
    }

    public function getWebPhotoPath()
    {
        return null === $this->photo ? null : '/'.$this->getUploadDir().'/'.$this->photo;
    }

    public function getUploadRootDir()
    {
        // le chemin absolu du répertoire où les documents uploadés doivent être sauvegardés
        return __DIR__.'/../../../web/'.$this->getUploadDir();
    }

    public function getUploadDir()
    {
        // on se débarrasse de « __DIR__ » afin de ne pas avoir de problème lorsqu'on affiche
        // le document/image dans la vue.
        return 'images/cars';
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     * @return Car
     */
    public function setUser(\AppBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }
}
