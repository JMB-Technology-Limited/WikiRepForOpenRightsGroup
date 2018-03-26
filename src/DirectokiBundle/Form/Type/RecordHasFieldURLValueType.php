<?php

namespace DirectokiBundle\Form\Type;

use DirectokiBundle\Entity\RecordHasFieldURLValue;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackValidator;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\OptionsResolver\OptionsResolver;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class RecordHasFieldURLValueType extends BaseRecordHasFieldValueType {
    
    public function buildForm(FormBuilderInterface $builder, array $options) {


        $builder->add('value', UrlType::class, array(
            'required' => false,
            'label'=>'Value',
            'data' => $options['current']->getValue()
        ));


        parent::buildForm($builder, $options);


    }

    public function getName() {
        return 'tree';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'current'=>null,
        ));
    }


}
