<?php

namespace DirectokiBundle\Form\Type;

use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\RecordHasFieldLatLngValue;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackValidator;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class RecordHasFieldSelectValueType extends BaseRecordHasFieldValueType {

    protected $selectValues = array();


    public function buildForm(FormBuilderInterface $builder, array $options) {
        $repoSelectValue = $options['container']->get('doctrine')->getManager()->getRepository('DirectokiBundle:SelectValue');

        foreach($repoSelectValue->findByFieldSortForLocale($options['field'],$options['locale']) as $selectValue) {
            $this->selectValues[$selectValue->getCachedTitleForLocale($options['locale'])] = $selectValue;
        }

        $builder->add('value', ChoiceType::class, array(
            'required' => false,
            'label'=>'Value',
            'choices' => $this->selectValues,
            'data' =>$options['current']->getSelectValue(),
        ));

        parent::buildForm($builder, $options);

    }

    public function getName() {
        return 'tree';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'container'=>null,
            'field'=>null,
            'record'=>null,
            'locale'=>null,
            'current'=>null,
        ));
    }




}
