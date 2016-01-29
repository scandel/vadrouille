<?php

namespace AppBundle\Form\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use libphonenumber\PhoneNumberFormat;
use Symfony\Component\Validator\Constraints\True;

class RegistrationType extends AbstractType
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
        $builder->add('phone', 'tel', array(
            'label' => 'user.phone.label',
            'required' => false,
            'attr'=> array( 'help'=> 'De préférence un numéro de mobile (ce numéro sera affiché sur vos annonces si vous le souhaitez)' ),
            'default_region' => 'FR',
            'format' => PhoneNumberFormat::NATIONAL,
            ));

        // Terms checkbox - not mapped
        $builder->add('terms','checkbox', array(
            'mapped' => false,
            'label' => 'user.terms',
            'constraints' => array(new True(array(
                'message' => 'user.terms.unchecked',
                'groups' => array('AppRegistration'),
                ))),
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