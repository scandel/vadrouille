<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Entity\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;


/**
* @ORM\Entity
* @ORM\Table(name="Users")
* @ORM\HasLifecycleCallbacks
*/
class User extends BaseUser
{
    /**
    * @ORM\Id
    * @ORM\Column(type="integer")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;

    /**
     * @ORM\Column(type="string", length=1)
     */
    protected $gender;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $first_name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $last_name;

    /**
     * @ORM\Column(type="phone_number")
     */
    protected $phone;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    protected $birth_date;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $city;

    /**
     * @ORM\Column(type="text")
     */
    protected $bio = '';

    /**
     * @ORM\OneToMany(targetEntity="Car", mappedBy="user")
     */
    protected $cars;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $photo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $originalPhoto;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $updated_at;


    public function __construct()
    {
        parent::__construct();
        // your own logic

        // TODO : remove this but have a "no salt" logic for legacy users
        // No salt for legacy Users
        // $this->salt = '';

        $this->cars = new ArrayCollection();

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
     * Set first_name
     *
     * @param string $firstName
     * @return User
     */
    public function setFirstName($firstName)
    {
        $this->first_name = $firstName;

        return $this;
    }

    /**
     * Get first_name
     *
     * @return string 
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * Set last_name
     *
     * @param string $lastName
     * @return User
     */
    public function setLastName($lastName)
    {
        $this->last_name = $lastName;

        return $this;
    }

    /**
     * Get last_name
     *
     * @return string 
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * Redefines method so that email is also used as userName
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email)
    {
        parent::setUsername($email);
        return parent::setEmail($email);
    }

    /**
     * Redefines method so that emailCanonical is also used as userNameCanonical
     *
     * @param string $emailCanonical
     * @return $this
     */
    public function setEmailCanonical($emailCanonical)
    {
        parent::setUsernameCanonical($emailCanonical);
        return parent::setEmailCanonical($emailCanonical);
    }


    /**
     * Set gender
     *
     * @param string $gender
     * @return User
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Get gender
     *
     * @return string 
     */
    public function getGender()
    {
        return $this->gender;
    }


    /**
     * Set phone
     *
     * @param phone_number $phone
     * @return User
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return phone_number 
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set birth_date
     *
     * @param \DateTime $birthDate
     * @return User
     */
    public function setBirthDate($birthDate)
    {
        $this->birth_date = $birthDate;

        return $this;
    }

    /**
     * Get birth_date
     *
     * @return \DateTime 
     */
    public function getBirthDate()
    {
        return $this->birth_date;
    }

    /**
     * Set city
     *
     * @param string $city
     * @return User
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string 
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set bio
     *
     * @param string $bio
     * @return User
     */
    public function setBio($bio)
    {
        $this->bio = $bio;

        return $this;
    }

    /**
     * Get bio
     *
     * @return string 
     */
    public function getBio()
    {
        return $this->bio;
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
        return 'images/users';
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
        // Update "updatedAt" field
        $this->updated_at = new \DateTime();

        // Fills the photo field with man or woman avatar if photo is empty or is an avatar
        if (empty($this->photo) || substr($this->photo,0,7) == 'avatars') {
            $this->photo = ($this->gender == 'w') ? "avatars/woman.png" :  "avatars/man.png";
            $this->originalPhoto = $this->photo;
        }
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        /*if ($file = $this->getAbsolutePhotoPath()) {
            unlink($file);
        }*/
    }


    /**
     * Set updated_at
     *
     * @param \DateTime $updatedAt
     * @return User
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updated_at = $updatedAt;

        return $this;
    }

    /**
     * Get updated_at
     *
     * @return \DateTime 
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }


    /**
     * Set photo
     *
     * @param string $photo
     * @return User
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
     * @return User
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
     * Add cars
     *
     * @param \AppBundle\Entity\Car $cars
     * @return User
     */
    public function addCar(\AppBundle\Entity\Car $cars)
    {
        $this->cars[] = $cars;

        return $this;
    }

    /**
     * Remove cars
     *
     * @param \AppBundle\Entity\Car $cars
     */
    public function removeCar(\AppBundle\Entity\Car $cars)
    {
        $this->cars->removeElement($cars);
    }

    /**
     * Get cars
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCars()
    {
        return $this->cars;
    }
}
