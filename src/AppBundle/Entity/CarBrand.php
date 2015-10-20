<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * CarBrand
 *
 * @ORM\Table(name="CarBrands")
 * @ORM\Entity
 */
class CarBrand
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="CarModel", mappedBy="brand")
     */
    private $models;


    public function __construct()
    {
        $this->models = new ArrayCollection();
    }

    /**
     * Set Id
     * (not working when AI activated)
     *
     * @param integer $id
     * @return CarBrand
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
     * Set name
     *
     * @param string $name
     * @return CarBrand
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
     * Add models
     *
     * @param \AppBundle\Entity\CarModel $models
     * @return CarBrand
     */
    public function addModel(\AppBundle\Entity\CarModel $models)
    {
        $this->models[] = $models;

        return $this;
    }

    /**
     * Remove models
     *
     * @param \AppBundle\Entity\CarModel $models
     */
    public function removeModel(\AppBundle\Entity\CarModel $models)
    {
        $this->models->removeElement($models);
    }

    /**
     * Get models
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getModels()
    {
        return $this->models;
    }
}
