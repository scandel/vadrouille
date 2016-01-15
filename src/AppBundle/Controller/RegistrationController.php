<?php

namespace AppBundle\Controller;

use FOS\UserBundle\Controller\RegistrationController as BaseController;
use Symfony\Component\HttpFoundation\RedirectResponse;

class RegistrationController extends BaseController
{
    /*
    public function registerAction()
    {
        $form = $this->container->get('fos_user.registration.form');
        $formHandler = $this->container->get('fos_user.registration.form.handler');
        $confirmationEnabled = $this->container->getParameter('fos_user.registration.confirmation.enabled');

        $process = $formHandler->process($confirmationEnabled);
        if ($process) {
            $user = $form->getData();

            $authUser = false;
            if ($confirmationEnabled) {
                $this->container->get('session')->set('fos_user_send_confirmation_email/email', $user->getEmail());
                $route = 'fos_user_registration_check_email';
            } else {
                $authUser = true;
                $route = 'fos_user_registration_confirmed';
                // Custom : send welcome email (email validation not mandatory)
                // Set a token to the user
                $userManager = $container->get('fos_user.user_manager');
                $theUser = $userManager->findUserByEmail($user->getEmail());
                if (null === $theUser->getConfirmationToken()) {
                    // todo : pass the token generator and generate a token
                   //  $theUser->setConfirmationToken($this->tokenGenerator->generateToken());
                    // todo : uses this token in the validation email
                }
                $this->sendWelcomeEmail($user);
            }

            $this->setFlash('fos_user_success', 'registration.flash.user_created');
            $url = $this->container->get('router')->generate($route);
            $response = new RedirectResponse($url);

            if ($authUser) {
                $this->authenticateUser($user, $response);
            }

            return $response;
        }

        return $this->container->get('templating')->renderResponse('FOSUserBundle:Registration:register.html.'.$this->getEngine(), array(
            'form' => $form->createView(),
        ));
    }
*/
    // Send welcome and email confirmation email
    public function sendWelcomeEmail($user)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject('Bienvenue')
            ->setFrom('contact@vadrouille-covoiturage.com')
            ->setTo($user->getEmail())
            ->setBody(
                $this->container->get('templating')
                    ->renderResponse('emails/registration.html.twig', array(
                        'user' => $user,
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

        $mailer = $this->container->get('mailer');
        $mailer->send($message);
    }

}
