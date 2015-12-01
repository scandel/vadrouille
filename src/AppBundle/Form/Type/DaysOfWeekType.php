<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DaysOfWeekType extends AbstractType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'choices' => array(
                1 => 'Lun.',
                2 => 'Mar.',
                3 => 'Mer.',
                4 => 'Jeu.',
                5 => 'Ven.',
                6 => 'Sam.',
                7 => 'Dim.',
            ),
            'multiple' => true,
            'expanded' => true,
        ));
    }
    public function getParent()
    {
        return 'choice';
    }

    public function getName()
    {
        return 'daysOfWeek';
    }
}