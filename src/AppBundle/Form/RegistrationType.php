<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Adds fields : gender, first_name, last_name
        $builder->add('gender', 'choice', array(
            'label' => 'user.gender',
            'choices' => array('m' => 'Monsieur', 'w' => 'Madame/Mademoiselle'),
            ));
        $builder->add('first_name', null, array(
            'label' => 'user.first_name'
            )) ;
        $builder->add('last_name', null, array(
            'label' => 'user.last_name'
            ));
        $builder->add('phone', null, array(
            'label' => 'user.phone'
            ));

        // Removes username as email is used instead
        $builder->remove('username');
    }

    public function getParent()
    {
        return 'fos_user_registration';
    }

    public function getName()
    {
        return 'app_user_registration';
    }
}