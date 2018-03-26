<?php

namespace DirectokiBundle\Form\Type;

use DirectokiBundle\Entity\DataHasStringField;
use DirectokiBundle\Entity\RecordHasState;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackValidator;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class RecordEditStateType extends AbstractType {




    public function buildForm(FormBuilderInterface $builder, array $options) {


        $builder->add('approve',  CheckboxType::class, array(
            'required' => false,
            'label'=>'Approve instantly?',
            'data' =>true,
        ));

        $builder->add('createdComment', TextareaType::class, array(
            'required' => false,
            'label' => 'Comment on change'
        ));


    }

    public function getName() {
        return 'tree';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'current' => null,
        ));
    }
}




