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
class ProjectSettingsEditType extends AbstractType {


    public function buildForm(FormBuilderInterface $builder, array $options) {


        $builder->add('APIReadAllowed',  CheckboxType::class, array(
            'required'=> false,
            'label'=>'API Read Allowed?',
        ));


        $builder->add('APIModeratedEditAllowed',  CheckboxType::class, array(
            'required'=> false,
            'label'=>'API ModeratedEdit Allowed?',
        ));


        $builder->add('APIReportAllowed',  CheckboxType::class, array(
            'required'=> false,
            'label'=>'API Report Allowed?',
        ));

        $builder->add('WebReadAllowed',  CheckboxType::class, array(
            'required'=> false,
            'label'=>'Web Read Allowed?',
        ));


        $builder->add('WebModeratedEditAllowed',  CheckboxType::class, array(
            'required'=> false,
            'label'=>'Web ModeratedEdit Allowed?',
        ));


        $builder->add('WebReportAllowed',  CheckboxType::class, array(
            'required'=> false,
            'label'=>'Web Report Allowed?',
        ));

    }

    public function getName() {
        return 'tree';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
        ));
    }
}

