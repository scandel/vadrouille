<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;

/**
 * Trip
 *
 * @ORM\Table(name="Trips")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\TripRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Trip
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
     * @ORM\Column(name="status", type="string", length=10)
     */
    private $status = "OK";

    /**
     * @var boolean
     *
     * @ORM\Column(name="current", type="boolean")
     */
    private $current = true ;


    /**
     * @var
     *
     * @ORM\OneToMany(targetEntity="Stop", mappedBy="trip", cascade={"persist", "remove"})
     */
    private $stops;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    private $comment;



    public function __construct()
    {
        $this->stops = new ArrayCollection();
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
     * Set status
     *
     * @param string $status
     * @return Trip
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
     * Set current
     *
     * @param boolean $current
     * @return Trip
     */
    public function setCurrent($current)
    {
        $this->current = $current;

        return $this;
    }

    /**
     * Get current
     *
     * @return boolean 
     */
    public function getCurrent()
    {
        return $this->current;
    }

    /**
     * Set comment
     *
     * @param string $comment
     * @return Trip
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
     * Add stops
     *
     * @param \AppBundle\Entity\Stop $stops
     * @return Trip
     */
    public function addStop(\AppBundle\Entity\Stop $stops)
    {
        $this->stops[] = $stops;

        return $this;
    }

    /**
     * Remove stops
     *
     * @param \AppBundle\Entity\Stop $stops
     */
    public function removeStop(\AppBundle\Entity\Stop $stops)
    {
        $this->stops->removeElement($stops);
    }

    /**
     * Get stops
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getStops()
    {
        return $this->stops;
    }

    /**
     * Order $stops by delta
     *
     * @ORM\PostLoad
     */
    public function orderStops()
    {
        $stops = array();
        foreach($this->stops as $stop) {
            $stops[$stop->getDelta()] = $stop;
        }
        ksort($stops);
        $this->stops->clear();
        foreach($stops as $stop) {
            $this->stops->add($stop);
        }
    }

}
