<?php



namespace DirectokiBundle\Form\Type;

use DirectokiBundle\Entity\DataHasStringField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackValidator;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormError;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class PublicRecordNewType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {

        foreach ( $options['fields'] as $field ) {

            $fieldType = $options['container']->get('directoki_field_type_service')->getByField($field);

            $fieldType->addToPublicNewRecordForm($field, $builder, $options['locale']);

        }

        if (!$options['user']) {
            $builder->add('email', EmailType::Class, array(
                'required' => false,
                'label' => 'Your Email',
            ));

            $builder->add('human', TextType::class, array(
                'required' => true,
                'label'=>'1+1=',
            ));

            /** @var \closure $myExtraFieldValidator **/
            $myExtraFieldValidator = function(FormEvent $event) {
                $form = $event->getForm();
                $human = $form->get('human')->getData();
                if ($human != '2') {
                    $form['human']->addError(new FormError("Please show you are a human (Sorry!)"));
                }

            };

            $builder->addEventListener(FormEvents::POST_SUBMIT, $myExtraFieldValidator);
        }

        $builder->add('comment', TextareaType::class, array(
            'required' => false,
            'label'=>'Comment',
        ));
    }

    public function getName() {
        return 'report';
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'user' => null,
            'fields' => null,
            'container' => null,
            'locale' => null,
        ));
    }

}




