<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * City
 *
 * @ORM\Table(name="Cities")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\CityRepository")
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
     * @ORM\OneToMany(targetEntity="CityName", mappedBy="city")
     */
    private $names;

    /**
     * @ORM\ManyToOne(targetEntity="Country")
     * @ORM\JoinColumn(name="country_code", referencedColumnName="code")
     */
    private $country;

    /**
     * @var string
     *
     * @ORM\Column(name="postCode", type="string", length=10)
     */
    private $postCode;

    /**
     * @var string
     *
     * @ORM\Column(name="lat", type="decimal", precision=7, scale=5)
     */
    private $lat;

    /**
     * @var string
     *
     * @ORM\Column(name="lng", type="decimal", precision=7, scale=5)
     */
    private $lng;

    /**
     * @var integer
     *
     * @ORM\Column(name="note", type="integer")
     */
    private $note;


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
     * Set lat
     *
     * @param string $lat
     * @return City
     */
    public function setLat($lat)
    {
        $this->lat = $lat;

        return $this;
    }

    /**
     * Get lat
     *
     * @return string 
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * Set lng
     *
     * @param string $lng
     * @return City
     */
    public function setLng($lng)
    {
        $this->lng = $lng;

        return $this;
    }

    /**
     * Get lng
     *
     * @return string 
     */
    public function getLng()
    {
        return $this->lng;
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

    public function getMainName($language = "")
    {
        $language = ($language) ? $language : 'fr';
        $namesInLanguage = array();
        $mainNameInLanguage = "";
        foreach ($this->names as $name) {
            if ($name->getLanguage() == $language) {
                $namesInLanguage[] = $name ;
                if ($name->isMain()) {
                    $mainNameInLanguage = $name;
                }
            }
        }
        if ($mainNameInLanguage) {
            return $mainNameInLanguage;
        }
        else {
            return $namesInLanguage[0];
        }
    }
}
