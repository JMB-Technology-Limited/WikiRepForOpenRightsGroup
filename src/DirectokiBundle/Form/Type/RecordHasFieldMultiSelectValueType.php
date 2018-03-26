<?php

namespace DirectokiBundle\Form\Type;

use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\Record;
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
class RecordHasFieldMultiSelectValueType extends BaseRecordHasFieldValueType {

    protected $selectValues = array();

    protected $selectValuesCurrentValue = array();


    public function buildForm(FormBuilderInterface $builder, array $options) {
        $repoSelectValue = $options['container']->get('doctrine')->getManager()->getRepository('DirectokiBundle:SelectValue');
        $repoRecordHasFieldMultiSelectValue = $options['container']->get('doctrine')->getManager()->getRepository('DirectokiBundle:RecordHasFieldMultiSelectValue');

        foreach($repoSelectValue->findByFieldSortForLocale($options['field'],$options['locale']) as $selectValue) {
            $this->selectValues[] = $selectValue;
            $this->selectValuesCurrentValue[$selectValue->getPublicId()] = $repoRecordHasFieldMultiSelectValue->doesRecordHaveFieldHaveValue($options['record'], $options['field'], $selectValue);
        }

        foreach($this->selectValues as $selectValue) {

            $builder->add('value_'. $selectValue->getPublicId(), CheckboxType::class, array(
                'required' => false,
                'label'=>$selectValue->getCachedTitleForLocale($options['locale']),
                'data' =>$this->selectValuesCurrentValue[$selectValue->getPublicId()],
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
            'container'=>null,
            'field'=>null,
            'record'=>null,
            'locale'=>null,
        ));
    }




}
