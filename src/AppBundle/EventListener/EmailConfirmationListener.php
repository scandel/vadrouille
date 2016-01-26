<?php
namespace AppBundle\EventListener;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use FOS\UserBundle\Model\UserManager;

/**
 * Listener responsible to send a "confirm your mail" email after registration
 * And to set email non-confirmed after email is changed in profile
 */
class EmailConfirmationListener implements EventSubscriberInterface
{
    private $userManager;
    private $tokenGenerator;
    private $session;
    private $router;
    private $mailer;
    private $templating;
    private $oldEmail;

    public function __construct(UserManager $userManager,
                                TokenGeneratorInterface $tokenGenerator,
                                SessionInterface $session,
                                UrlGeneratorInterface $router,
                                $mailer,
                                EngineInterface $templating )
    {
        $this->userManager = $userManager;
        $this->tokenGenerator = $tokenGenerator;
        $this->session = $session;
        $this->router = $router;
        $this->mailer = $mailer;
        $this->templating = $templating;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            // Send a confirmation email after registration
            FOSUserEvents::REGISTRATION_COMPLETED => 'onRegistrationCompleted',
            // Set email to non-confirmed after it changes in profile edit
            FOSUserEvents::PROFILE_EDIT_INITIALIZE => 'onProfileEditInitialize',
            FOSUserEvents::PROFILE_EDIT_SUCCESS => 'onProfileEditSuccess'
        );
    }

    public function onRegistrationCompleted(FilterUserResponseEvent $event)
    {
        $user = $event->getUser();

        if (null === $user->getConfirmationToken()) {
            $user->setConfirmationToken($this->tokenGenerator->generateToken());
            $this->userManager->updateUser($user);
        }
        $this->sendWelcomeEmail($user);
        $this->session->set('fos_user_send_confirmation_email/email', $user->getEmail());
        //$url = $this->router->generate('homepage');
        //$event->setResponse(new RedirectResponse($url));
    }

    // Send welcome and email confirmation email
    public function sendWelcomeEmail($user)
    {
        $url = $this->router->generate('user_confirm_email',
            array('token' => $user->getConfirmationToken()),
            UrlGeneratorInterface::ABSOLUTE_URL);
        $message = \Swift_Message::newInstance()
            ->setSubject('Bienvenue')
            ->setFrom('contact@vadrouille-covoiturage.com')
            ->setTo($user->getEmail())
            ->setBody(
                $this->templating
                    ->renderResponse('emails/registration.html.twig', array(
                        'user' => $user,
                        'url' => $url,
                    ))
            )
            /*
             * If you also want to include a plaintext version of the message
            ->addPart(
                $this->renderView(
                    'Emails/registration.txt.twig',
                    array('name' => $name)
                ),
                'text/plain'
            )
            */
        ;
        $this->mailer->send($message);
    }

    public function onProfileEditInitialize(GetResponseUserEvent $event)
    {
        $this->oldEmail = $event->getUser()->getEmail();
    }

    public function onProfileEditSuccess(FormEvent $event)
    {
        $user = $event->getForm()->getData();
        if ($user->getEmail() !== $this->oldEmail)
        {
            // set email confirmed to "no"
            $user->setConfirmationToken(null);
            $user->setEmailConfirmed(null);
            $this->userManager->updateUser($user);

            // Notice
            $this->session->getFlashBag()->add(
                'warning',
                'Attention, vous avez changé votre adresse email, pensez à la reconfirmer.'
            );
        }
    }

}