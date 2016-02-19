<?php

namespace AppBundle\Form\Person;

use Symfony\Component\Form\AbstractType;
use AppBundle\Form\Guest\GuestType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use libphonenumber\PhoneNumberFormat;
use Symfony\Component\Validator\Constraints\True;

class PersonGuestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Just embed a Guest Form ...
        $builder->add('guest', GuestType::class, array(
            'label' => false
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Person',
        ));
    }

    public function getName()
    {
        return 'app_user_person_guest';
    }
}