<?php

namespace AppBundle\Form\User;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use AppBundle\Form\City\CityType;
use AppBundle\Form\DataTransformer\CityTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use libphonenumber\PhoneNumberFormat;
use Doctrine\ORM\EntityManager;

class ProfileType extends AbstractType
{
    private $manager;

    public function __construct(EntityManager $manager) {
        $this->manager = $manager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Adds fields : gender, first_name, last_name, phone, birthDate, city, bio
        $builder->add('gender', ChoiceType::class, array(
            'label' => 'user.gender.label',
            'choices' => array(
                'user.gender.male' => 'm',
                'user.gender.female' => 'w'
            ),
            'choices_as_values' => true,
            'expanded' => true,
            'multiple' => false,
        ));

        $builder->add('firstName', TextType::class, array(
            'label' => 'user.firstName.label'
        )) ;

        $builder->add('lastName', TextType::class, array(
            'label' => 'user.lastName.label'
        ));

        $builder->add('phone', PhoneNumberType::class, array(
            'label' => 'user.phone.label',
            'required' => false,
            'attr'=> array(
                'help'=> 'De préférence un numéro de mobile (ce numéro sera affiché sur vos annonces si vous le souhaitez)'
            ),
            'default_region' => 'FR',
            'format' => PhoneNumberFormat::NATIONAL,
        ));

        $currYear = (int)date('Y');
        $builder->add('birthDate', BirthdayType::class, array(
            'label' => 'user.birthDate.label',
            'required' => false,
            'attr'=> array(
                'help'=> 'Si vous remplissez ce champ, votre âge sera affiché sur votre profil public. Votre date de naissance, elle, ne sera jamais affichée.'
            ),
            'input' => 'datetime',
            'widget' => 'choice',
            'years' => range($currYear-18, $currYear-100, -1),
        ));

        $builder->add('city', CityType::class, array(
            'label' => 'user.city.label',
            'required' => false,
            'invalid_message' => 'Cette ville n\'est pas reconnue, merci d\'en choisir une parmi les propositions de l\'autocomplétion.' ,
        ));
        $builder->get('city')
            ->addModelTransformer(new CityTransformer($this->manager));

        $builder->add('bio', TextareaType::class, array(
            'label' => 'user.bio.label',
            'attr'=> array(
                'help'=> 'Par exemple : que faîtes-vous dans la vie, quel est votre tempérament de covoitureur, pourquoi vous faîtes du covoiturage...',
            ),
            'required' => false,
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
