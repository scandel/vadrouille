<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;


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
     * @ORM\OneToOne(targetEntity="Person", mappedBy="user", cascade={"persist", "remove"})
     */
    protected $person;

    /**
     * @ORM\Column(type="string", length=1)
     */
    protected $gender;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $firstName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $lastName;

    /**
     * @ORM\Column(type="phone_number")
     */
    protected $phone;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    protected $birthDate;

    /**
     * @ORM\ManyToOne(targetEntity="City")
     * @ORM\JoinColumn(name="city_id", referencedColumnName="id", nullable=true)
     */
    private $city;

    /**
     * @ORM\Column(type="text", nullable=true)
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
     * @var NULL or date at wich email has been confirmed
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $emailConfirmed;

    /**
     * @var string : comment visible only in admin (security)
     * @ORM\Column(type="text")
     */
    protected $comment = '';

    /**
     * @var array : other ids known for this user
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $multipleIds;

    /**
     * @var \DateTime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @var \DateTime $updated
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private $updated;

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


    public function getPerson()
    {
        return $this->person;
    }

    /**
     * Set first_name
     *
     * @param string $firstName
     * @return User
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get first_name
     *
     * @return string 
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set last_name
     *
     * @param string $lastName
     * @return User
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get last_name
     *
     * @return string 
     */
    public function getLastName()
    {
        return $this->lastName;
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
     * Set birthDate
     *
     * @param \DateTime $birthDate
     * @return User
     */
    public function setBirthDate($birthDate)
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    /**
     * Get birthDate
     *
     * @return \DateTime 
     */
    public function getBirthDate()
    {
        return $this->birthDate;
    }

    /**
     * Get age calculated from birthDate
     *
     * @return mixed
     */
    public function getAge()
    {
        if (!$this->birthDate)
            return null;

        $from = $this->birthDate;
        $to   = new \DateTime('today');
        return $from->diff($to)->y;
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

    public function getHtmlPhoto()
    {
        return null === $this->photo ? '' : '<img src="'.$this->getWebPhotoPath().'" />';
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
     * Get created_at
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->created;
    }

    /**
     * Get updated_at
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updated;
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

    /**
     * Set status
     *
     * @param string $status
     * @return User
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set comment
     *
     * @param string $comment
     * @return User
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string 
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set multipleIds
     *
     * @param string $multipleIds
     * @return User
     */
    public function setMultipleIds($multipleIds)
    {
        $this->multipleIds = $multipleIds;

        return $this;
    }

    /**
     * Get multipleIds
     *
     * @return string 
     */
    public function getMultipleIds()
    {
        return $this->multipleIds;
    }

    /**
     * Set emailConfirmed
     *
     * @param \DateTime $emailConfirmed
     * @return User
     */
    public function setEmailConfirmed($emailConfirmed)
    {
        $this->emailConfirmed = $emailConfirmed;

        return $this;
    }

    /**
     * Get emailConfirmed
     *
     * @return \DateTime 
     */
    public function getEmailConfirmed()
    {
        return $this->emailConfirmed;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return User
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     * @return User
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime 
     */
    public function getUpdated()
    {
        return $this->updated;
    }


    /**
     * Set person
     *
     * @param \AppBundle\Entity\Person $person
     * @return User
     */
    public function setPerson(\AppBundle\Entity\Person $person = null)
    {
        $this->person = $person;

        return $this;
    }
}
