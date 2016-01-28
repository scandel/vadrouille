<?php

namespace AppBundle\Utils;

// use Symfony\Component\DependencyInjection\ContainerInterface as Container;

class MailManager
{
    protected $mailer;
    protected $twig;
    // private $container;
    protected $contactEmail;
    protected $fromEmail;
    protected $fromName;

    public function __construct(\Swift_Mailer $mailer, \Twig_Environment $twig, $contactEmail, $fromEmail, $fromName)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->contactEmail = $contactEmail;
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
    }

    /**
     * Send email
     *
     * @param   string   $template      email template
     * @param   mixed    $parameters    custom params for template
     * @param   string   $to            to email address or array of email addresses
     * @param   string   $from          from email address
     * @param   string   $fromName      from name
     *
     * @return  boolean                 send status
     */
    public function sendEmail($template, $parameters, $to, $from = null, $fromName = null)
    {
        $template = $this->twig->loadTemplate('emails/' . $template . '.html.twig');

        $subject  = $template->renderBlock('subject', $parameters);
        $bodyHtml = $template->renderBlock('body_html', $parameters);
        $bodyText = $template->renderBlock('body_text', $parameters);

        $fromName = ($fromName) ? $fromName : $this->fromName;
        $from = ($from) ? $from : $this->fromEmail;

        try {
            $message = \Swift_Message::newInstance()
                ->setSubject($subject)
                ->setFrom($from, $fromName)
                ->setTo($to)
                ->setBody($bodyHtml, 'text/html')
                ->addPart($bodyText, 'text/plain')
            ;
            $response = $this->mailer->send($message);

        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
        return $response;
    }
}