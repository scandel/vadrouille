<?php

namespace AppBundle\Form\Guest;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use libphonenumber\PhoneNumberFormat;
use Symfony\Component\Validator\Constraints\True;

class GuestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Adds fields : gender, first_name, last_name
        $builder->add('gender', 'choice', array(
            'label' => 'user.gender.label',
            'choices' => array('m' => 'user.gender.male', 'w' => 'user.gender.female'),
            'expanded' => true,
            'multiple' => false,
            ));
        $builder->add('firstName', null, array(
            'label' => 'user.firstName.label'
            )) ;
        $builder->add('lastName', null, array(
            'label' => 'user.lastName.label'
            ));
        $builder->add('email', null, array(
            'label' => 'user.email.label'
        ));
        $builder->add('phone', 'tel', array(
            'label' => 'user.phone.label',
            'required' => false,
            'attr'=> array( 'help'=> 'De préférence un numéro de mobile (ce numéro sera affiché sur vos annonces si vous le souhaitez)' ),
            'default_region' => 'FR',
            'format' => PhoneNumberFormat::NATIONAL,
            ));
    }

    public function getName()
    {
        return 'app_user_guest';
    }
}