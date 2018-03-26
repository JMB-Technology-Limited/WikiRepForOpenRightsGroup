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
class PublicRecordReportType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {


        $builder->add('description', TextareaType::class, array(
            'required' => true,
            'label'=>'Report',
        ));

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

    }

    public function getName() {
        return 'report';
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'user' => null,
        ));
    }

}




