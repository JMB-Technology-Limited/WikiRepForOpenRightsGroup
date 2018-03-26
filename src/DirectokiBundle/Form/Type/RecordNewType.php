<?php

namespace DirectokiBundle\Form\Type;

use DirectokiBundle\Entity\DataHasStringField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackValidator;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class RecordNewType extends AbstractType {



    public function buildForm(FormBuilderInterface $builder, array $options) {


        foreach($options['fields'] as $field) {

            $fieldType = $options['container']->get('directoki_field_type_service')->getByField($field);

            $fieldType->addToNewRecordForm($field, $builder, $options['locale']);

        }


        $builder->add('comment', TextareaType::class, array(
            'required' => false,
            'label' => 'Comment'
        ));

        $builder->add('approve',  CheckboxType::class, array(
            'required' => false,
            'label'=>'Approve instantly?',
            'data' =>true,
        ));

    }

    public function getName() {
        return 'tree';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'container' => null,
            'fields' => null,
            'locale' => null,
        ));
    }

}
