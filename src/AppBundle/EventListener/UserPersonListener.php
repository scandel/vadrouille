<?php
namespace AppBundle\EventListener;

use AppBundle\Entity\Person;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use Doctrine\ORM\EntityManager;

/**
 * Listener responsible to create a Person object after registration
 */
class UserPersonListener implements EventSubscriberInterface
{
    private $entityManager;

    public function __construct(EntityManager $em )
    {
        $this->entityManager = $em;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            // Create a person linked to the user
            FOSUserEvents::REGISTRATION_COMPLETED => 'onRegistrationCompleted',
        );
    }

    /**
     * Create a new Person linked to the User
     * and persist it to database
     *
     * @param FilterUserResponseEvent $event
     */
    public function onRegistrationCompleted(FilterUserResponseEvent $event)
    {
        $user = $event->getUser();
        $person = new Person($user);
        $this->entityManager->persist($person);
        $this->entityManager->flush();
    }
}