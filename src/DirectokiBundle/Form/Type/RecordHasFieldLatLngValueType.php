<?php

namespace DirectokiBundle\Form\Type;

use DirectokiBundle\Entity\RecordHasFieldLatLngValue;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackValidator;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\OptionsResolver\OptionsResolver;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class RecordHasFieldLatLngValueType extends BaseRecordHasFieldValueType {

    public function buildForm(FormBuilderInterface $builder, array $options) {


        $builder->add('lat', NumberType::class, array(
            'required' => false,
            'label'=>'Lat',
            'scale'=>12,
            'data' => $options['current']->getLat()
        ));

        $builder->add('lng', NumberType::class, array(
            'required' => false,
            'label'=>'Lng',
            'scale'=>12,
            'data' => $options['current']->getLng()
        ));


        parent::buildForm($builder, $options);

    }

    public function getName() {
        return 'latlng';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'current'=>null,
        ));
    }

}
