<?php

namespace DirectokiBundle\Form\Type;

use DirectokiBundle\Entity\RecordHasFieldStringValue;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackValidator;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class RecordHasFieldStringWithLocaleValueType extends BaseRecordHasFieldValueType {



    public function buildForm(FormBuilderInterface $builder, array $options) {

        foreach($options['locales'] as $locale) {

            $builder->add('value_'.$locale->getPublicId(), TextType::class, array(
                'required' => false,
                'label' => 'Value ('.$locale->getTitle().')',
                'data' => $options['values'][$locale->getPublicId()],
            ));

        }

        parent::buildForm($builder, $options);


    }

    public function getName() {
        return 'tree';
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'locales'=>null,
            'values'=>null,
        ));
    }

}
