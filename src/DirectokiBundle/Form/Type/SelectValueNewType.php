<?php

namespace DirectokiBundle\Form\Type;

use DirectokiBundle\Entity\DataHasStringField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackValidator;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class SelectValueNewType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {

        foreach($options['locales'] as $locale) {

            $builder->add('title_'.$locale->getId(), TextType::class, array(
                'required' => true,
                'label'=>'Title ('.$locale->getTitle().')',
                'mapped'=>false,
            ));

        }

    }

    public function getName() {
        return 'tree';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'locales' => null,
        ));
    }

}
