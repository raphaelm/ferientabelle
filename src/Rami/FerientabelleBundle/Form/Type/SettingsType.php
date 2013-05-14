<?php
namespace Rami\FerientabelleBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface; 

class SettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', array(
					'label' => 'Name'
				));
        $builder->add('email', 'email', array(
					'label' => 'E-Mail-Adresse',
					'required' => false
				));
    }

    public function getName()
    {
        return 'settings';
    }
}
