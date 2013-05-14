<?php
namespace Rami\FerientabelleBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface; 

class FriendType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('publicurl', 'url', array(
					'label' => 'Öffentliche Adresse',
					'attr'=> array('class'=>'input-long')
				));
    }

    public function getName()
    {
        return 'friend';
    }
}
