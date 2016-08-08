<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Trip
 *
 * @ORM\Table(name="prod.Trips")
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
     * @var Person
     *
     * @ORM\ManyToOne(targetEntity="Person", inversedBy="trips", cascade={"persist"})
     */
    private $person;

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
     * @var integer: Number of places proposed
     *
     * @ORM\Column(name="places", type="integer")
     */
    private $places = 3;

    /**
     * @var boolean : trip is full booked
     *
     * @ORM\Column(name="full", type="boolean")
     */
    private $full = false;

    /**
     * @var text : Choice small, medium, big
     *
     * @ORM\Column(name="bags", type="string", length=6)
     */
    private $bags = 'medium';

    /**
     * @var text : contact mode, Choice phone, email, both
     *
     * @ORM\Column(name="contact", type="string", length=5)
     */
    private $contact = 'both';

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    private $comment;

    /**
     * @var string : serialized JS roadbook object (from Mappy)
     *
     * @ORM\Column(name="mappy_roadbook", type="text", nullable=true)
     */
    private $mappyRoadbook;

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
        $this->endDate->add(new \DateInterval('P1M'));
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
     * @return int : number of stops
     */
    public function numberOfStops()
    {
        return count($this->stops);
    }

    /**
     * Order $stops by delta
     * @var $setDelta bool : if true, re-num deltas
     *
     * @ORM\PostLoad
     */
    public function orderStops($setDeltas = false)
    {
        $stops = array();
        foreach($this->stops as $stop) {
            $stops[$stop->getDelta()] = $stop;
        }
        ksort($stops);
        $this->stops->clear();
        $delta = 0;
        foreach($stops as $stop) {
            if ($setDeltas) {
                $stop->setDelta($delta);
                $delta++;
            }
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


    /**
     * Set nextDateTime
     *
     * @param \DateTime $nextDateTime
     * @return Trip
     */
    public function setNextDateTime($nextDateTime)
    {
        $this->nextDateTime = $nextDateTime;

        return $this;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Trip
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
     * @return Trip
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
     * @return Trip
     */
    public function setPerson(\AppBundle\Entity\Person $person = null)
    {
        $this->person = $person;

        return $this;
    }

    /**
     * Get person
     *
     * @return \AppBundle\Entity\Person 
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * Set mappyRoadbook
     *
     * @param string $mappyRoadbook
     * @return Trip
     */
    public function setMappyRoadbook($mappyRoadbook)
    {
        $this->mappyRoadbook = $mappyRoadbook;

        return $this;
    }

    /**
     * Get mappyRoadbook
     *
     * @return string 
     */
    public function getMappyRoadbook()
    {
        return $this->mappyRoadbook;
    }

    /**
     * Set places
     *
     * @param integer $places
     * @return Trip
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
     * Set full
     *
     * @param boolean $full
     * @return Trip
     */
    public function setFull($full)
    {
        $this->full = $full;

        return $this;
    }

    /**
     * Get full
     *
     * @return boolean 
     */
    public function getFull()
    {
        return $this->full;
    }

    /**
     * Set bags
     *
     * @param string $bags
     * @return Trip
     */
    public function setBags($bags)
    {
        $this->bags = $bags;

        return $this;
    }

    /**
     * Get bags
     *
     * @return string 
     */
    public function getBags()
    {
        return $this->bags;
    }

    /**
     * Set contact
     *
     * @param string $contact
     * @return Trip
     */
    public function setContact($contact)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * Get contact
     *
     * @return string 
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Constructs trip url.
     * $stop1 and $stop2 are optional arguments describing which stops are departure and arrival
     * (if null, first and last stops will be used)
     * Si env=dev, rajout du app_dev.php dans le twig
     *
     * @param $stop1 : Stop | string (stop city slug) | integer, delta (nb of the stop in the stops :  0 = first, 1 = second ...)
     * @param $stop2 : Stop | string (stop city slug) | integer, delta (nb of the stop in the stops : 0 = first, 1 = second ...)
     *
     * returns string : the trip url, like : /covoiturage/stop1-city-name-slug/stop2-city-name-slug/tripid
     */
    public function getUrl($stop1 = null, $stop2 = null) {

        // get stops as an array
        $this->orderStops();
        $stops = $this->stops->getValues();

        // Stop 1 or first
        if ($stop1 == null) {
            $stop1 = $this->stops->first();
        }
        else {
            $stop1exists = false;
            foreach ($stops as $stop) {
                if (   ($stop1 instanceof Stop && $stop1->getId() == $stop->getId())
                    || (is_integer($stop1) && $stop1 == $stop->getDelta())  // We expect a delta)
                    || (is_string($stop1) && $stop1 = $stop->getCity()->getSlug()) ) {
                    $stop1exists = true;
                    $stop1 = $stop;
                    break;
                }
            }
            if (!$stop1exists) {
                $stop1 = $this->stops->first();
            }
        }

        // Stop 2 or last
        if ($stop2 == null) {
            $stop2 = $this->stops->last();
        }
        else {
            $stop2exists = false;
            foreach ($stops as $stop) {
                if (   ($stop2 instanceof Stop && $stop2->getId() == $stop->getId())
                    || (is_integer($stop2) && $stop2 == $stop->getDelta())  // We expect a delta)
                    || (is_string($stop2) && $stop2 = $stop->getCity()->getSlug()) ) {
                    $stop2exists = true;
                    $stop2 = $stop;
                    break;
                }
            }
            if (!$stop2exists) {
                $stop2 = $this->stops->last();
            }
        }

        // Build url
        $url = '/covoiturage'
             . '/' . $stop1->getCity()->getSlug()
             . '/' . $stop2->getCity()->getSlug()
             . '/' . $this->getId();

        return $url;
    }
}
