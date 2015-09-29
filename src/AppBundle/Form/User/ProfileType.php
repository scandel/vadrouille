<?php

namespace AppBundle\Form\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use libphonenumber\PhoneNumberFormat;

class ProfileType extends AbstractType
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
        $builder->add('first_name', null, array(
            'label' => 'user.first_name.label'
        )) ;
        $builder->add('last_name', null, array(
            'label' => 'user.last_name.label'
        ));
        $builder->add('phone', 'tel', array(
            'label' => 'user.phone.label',
            'required' => false,
            'attr'=> array( 'help'=> 'De préférence un numéro de mobile (ce numéro sera affiché sur vos annonces si vous le souhaitez)' ),
            'default_region' => 'FR',
            'format' => PhoneNumberFormat::NATIONAL,
        ));

        // Removes username as email is used instead
        $builder->remove('username');
        // Removes Password, as there is a page for this
        $builder->remove('current_password');

    }

    public function getParent()
    {
        return 'fos_user_profile';
    }


    public function getName()
    {
        return 'app_user_profile';
    }
}
