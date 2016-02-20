<?php
namespace AppBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Listener responsible to redirect the user after successful registration
 * - to the value of the "_redirect" form value if present
 * - to the member homepage if not
 */
class UserRedirectListener implements EventSubscriberInterface
{
    private $router;

    public function __construct(UrlGeneratorInterface $router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FOSUserEvents::REGISTRATION_SUCCESS => 'onRegistrationConfirm'
        );
    }

    public function onRegistrationConfirm(\FOS\UserBundle\Event\FormEvent $event)
    {
        // Get route given in $_POST['_redirect'], if existent
        $request = Request::createFromGlobals();
        $route = $request->request->get('_redirect', 'fos_user_profile_edit');
        $route = ($route) ? $route : 'fos_user_profile_edit';

        $url = $this->router->generate($route);

        $event->setResponse(new RedirectResponse($url));
    }
}