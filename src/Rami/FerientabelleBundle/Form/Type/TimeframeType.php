<?php
namespace Rami\FerientabelleBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface; 

class TimeframeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('from', 'date', array(
					'widget' => 'single_text', 
					'format' => 'dd.MM.yyyy',
					'label' => 'Vom',
					'attr'=> array('class'=>'datepicker dpfrom input-small')
				));
        $builder->add('to', 'date', array(
					'widget' => 'single_text', 
					'format' => 'dd.MM.yyyy',
					'label' => 'Bis',
					'attr'=> array('class'=>'datepicker dpto input-small')
				));
        $builder->add('availability', 'choice', array(
					'choices' => array(-1 => 'bin ich schon verplant', 1 => 'habe ich frei', 0 => 'ist es noch unklar'),
				));
        $builder->add('comment', 'text', array(
					'label' => 'Kommentar',
					'required' => false,
					'attr'=> array('class'=>'input-long')
				));
    }

    public function getName()
    {
        return 'task';
    }
}
