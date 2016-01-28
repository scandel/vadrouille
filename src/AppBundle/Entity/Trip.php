<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Gedmo\Mapping\Annotation as Gedmo;

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
     * @var Datetime
     *
     * @ORM\Column(name="dep_time", type="time")
     */
    private $depTime;

    /**
     * @var boolean
     *
     * @ORM\Column(name="regular", type="boolean")
     */
    private $regular = false;

    /**
     * @var Datetime
     *
     * @ORM\Column(name="dep_date", type="date")
     */
    private $depDate;

    /**
     * @var array
     * An array of 7 booleans, one for each day of the week, true if the
     * trip is to be done this day.
     *
     * @ORM\Column(name="days", type="simple_array", nullable=true)
     */
    private $days = array();

    /**
     * @var Datetime
     *
     * @ORM\Column(name="begin_date", type="date")
     */
    private $beginDate;

    /**
     * @var Datetime
     *
     * @ORM\Column(name="end_date", type="date")
     */
    private $endDate;

    /**
     * @var Datetime
     *
     * @ORM\Column(name="next_datetime", type="datetime")
     */
    private $nextDateTime;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    private $comment;

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
        $this->stops = new ArrayCollection();
        $this->depDate = new \DateTime('now');
        $this->beginDate = new \DateTime('now');
        $this->endDate = new \DateTime('now');
        $this->nextDateTime = new \DateTime('now');
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


    /**
     * Set depTime
     *
     * @param \DateTime $depTime
     * @return Trip
     */
    public function setDepTime($depTime)
    {
        $this->depTime = $depTime;

        return $this;
    }

    /**
     * Get depTime
     *
     * @return \DateTime 
     */
    public function getDepTime()
    {
        return $this->depTime;
    }

    /**
     * Set regular
     *
     * @param boolean $regular
     * @return Trip
     */
    public function setRegular($regular)
    {
        $this->regular = $regular;

        return $this;
    }

    /**
     * Get regular
     *
     * @return boolean 
     */
    public function getRegular()
    {
        return $this->regular;
    }

    /**
     * Set depDate
     *
     * @param \DateTime $depDate
     * @return Trip
     */
    public function setDepDate($depDate)
    {
        $this->depDate = $depDate;

        return $this;
    }

    /**
     * Get depDate
     *
     * @return \DateTime 
     */
    public function getDepDate()
    {
        return $this->depDate;
    }

    /**
     * Set days
     *
     * @param array $days
     * @return Trip
     */
    public function setDays($days)
    {
        $this->days = $days;

        return $this;
    }

    /**
     * Get days
     *
     * @return array 
     */
    public function getDays()
    {
        return $this->days;
    }

    /**
     * Set beginDate
     *
     * @param \DateTime $beginDate
     * @return Trip
     */
    public function setBeginDate($beginDate)
    {
        $this->beginDate = $beginDate;

        return $this;
    }

    /**
     * Get beginDate
     *
     * @return \DateTime 
     */
    public function getBeginDate()
    {
        return $this->beginDate;
    }

    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     * @return Trip
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate
     *
     * @return \DateTime 
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Compute nextDateTime
     * based on regular/unique, depDate or days, depTime,
     * begin and end date
     * Also compute current (boolean), true if trip is "to come"
     *
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     *
     * @param \DateTime $after
     * @return Trip
     */
    public function computeNextDateTime()
    {
        $after = new \DateTime('now');

        // regular trip
        if ($this->regular) {

            if ($after < $this->beginDate) {
                $after = $this->beginDate;
            }
            if ($this->endDate ==null || $after < $this->endDate ) {

                $lastSundayTS = strtotime("last Sunday", strtotime($after->format("Y-m-d")));
                $lastSundayTS += 3600 * intval($this->depTime->format("H")) + 60 * intval($this->depTime->format("i"));
                $lastSundayDate = date("c",$lastSundayTS);
                $lastSunday = date_create_immutable($lastSundayDate);

                // days of the trip in next week, +1 of next week
                $days = $this->days;
                $days[] = $days[0] + 7;
                array_unshift($days, 0);

                $depTimes = array();
                foreach ($days as $d) {
                    $depTimes[] = $lastSunday->add(new \DateInterval('P'.$d.'D'));
                }

                for ($i = 0; $i < count($depTimes)-1; $i++) {
                    if ( $depTimes[$i] <= $after && $after < $depTimes[$i+1]) {
                        $this->nextDateTime = $depTimes[$i+1];
                        break;
                    }
                }
            }
            // If $after > $endDate, on ne change pas le nextDate, qui est < after et donc current = false.
        }
        // one-shot date
        else {
             $this->nextDateTime = \DateTime::createFromFormat(
                                        "Y-m-d H:i",
                                        $this->depDate->format("Y-m-d ").
                                        $this->depTime->format("H:i"));
        }

        $this->current = $this->nextDateTime >= $after;
        return $this;
    }

    /**
     * Get nextDateTime
     *
     * @return \DateTime 
     */
    public function getNextDateTime()
    {
        return $this->nextDateTime;
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

}
