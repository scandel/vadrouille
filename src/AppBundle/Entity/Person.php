<?php

namespace AppBundle\Entity;

use AppBundle\Entity\User;
use AppBundle\Entity\Guest;
use Doctrine\ORM\Mapping as ORM;


/**
 * A person is a User or a Guest :
 * we store what kind it is and user or guest id
 * and have direct accessors to common properties
 *
* @ORM\Entity
* @ORM\Table(name="Persons")
*/
class Person
{
    /**
    * @ORM\Id
    * @ORM\Column(type="integer")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $isUser;

    /**
     * @ORM\OneToOne(targetEntity="User", inversedBy="person")
     */
    protected $user;

    /**
     * @ORM\OneToOne(targetEntity="Guest")
     */
    protected $guest;


    public function __construct($person) {
        $this->setPerson($person);
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
     * Sets person with a user or a guest
     *
     * @param mixed $person
     * @return $this|bool
     */
    public function setPerson($person) {
        if ($person instanceof User) {
            $this->isUser = true;
            $this->user = $person;
        }
        else if ($person instanceof Guest) {
            $this->isUser = false;
            $this->guest = $person;
        }
        else {
            return null;
        }
        return $this;
    }

    public function isUser() {
        return ($this->isUser && $this->user !== null);
    }

    public function isGuest() {
        return (!$this->isUser && $this->guest !== null);
    }

    public function getUser() {
        if ($this->isUser === true) {
            return $this->user;
        }
        return null;
    }

    public function getGuest() {
        if ($this->isUser === false) {
            return $this->guest;
        }
        return null;
    }

    public function getPerson() {
        if ($this->isUser()) {
            return $this->user;
        }
        else if ($this->isGuest()) {
            return $this->guest;
        }
        return null;
    }

    /********************************************
     * Accessors to common properties
     ********************************************/

    /**
     * Get first_name
     *
     * @return string 
     */
    public function getFirstName()
    {
        return $this->getPerson()->getFirstName();
    }

    /**
     * Get last_name
     *
     * @return string 
     */
    public function getLastName()
    {
        return $this->getPerson()->getLastName();
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->getPerson()->getEmail();
    }

    /**
     * Get gender
     *
     * @return string 
     */
    public function getGender()
    {
        return $this->getPerson()->getGender();
    }

    /**
     * Get phone
     *
     * @return phone_number 
     */
    public function getPhone()
    {
        return $this->getPerson()->getPhone();
    }

    /**
     * Get comment
     *
     * @return string 
     */
    public function getComment()
    {
        return $this->getPerson()->getComment();
    }

    /**
     * Get created
     *
     * @return \DateTime 
     */
    public function getCreated()
    {
        return $this->getPerson()->getCreated();
    }

    /**
     * Get updated
     *
     * @return \DateTime 
     */
    public function getUpdated()
    {
        return $this->getPerson()->getUpdated();
    }
}
