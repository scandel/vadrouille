<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * todo : ajouter un glyphicon calendrier dans un input group,
 * le calendrier doit être cliquable
 *
 * Class DatePickerType
 * @package AppBundle\Form\Type
 */

class DatePickerType extends AbstractType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'widget' => 'single_text',
            'format' => 'dd/MM/yyyy',
        ));
    }
    public function getParent()
    {
        return 'date';
    }

    public function getName()
    {
        return 'datePicker';
    }
}