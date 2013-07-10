<?php
namespace Insurance\ContentBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;

use Knp\Menu\ItemInterface as MenuItemInterface;

class RateAdmin extends Admin {

  protected function configureFormFields(FormMapper $form)
  {
    $form->add('value', null, array('label' => 'Значение'))
      ->add('type', 'choice',
        array(
          'choices' => array(
            'base' => 'Основной расчет',
            'ns' => 'Несчастный случай',
            'dgo' => 'Доп. гражданская ответственность',
            ),
          'label' => 'Тип коэффициента',
        )
      )
      ->add('code', null, array('label' => 'Символьный код'))
          ->setHelps(array('type' => 'Тип коэффициента для различных видов страховок'));
  }

  protected function configureListFields(ListMapper $list)
  {
    $list->addIdentifier('id')
      ->add('value', null, array('label' => 'Значение'))
      ->add('type', null, array(
          'label' => 'Тип коэффициента',
          'template' => 'InsuranceContentBundle:Helper:enum_field_list.html.twig',
          'choices' => array(
            'base' => 'Основной расчет',
            'ns' => 'Несчастный случай',
            'dgo' => 'Доп. гражданская ответственность',
            )
          )
        )
      ->add('code', null, array('label' => 'Символьный код'));
  }

  protected function configureDatagridFilters(DatagridMapper $filter)
  {
    $filter->add('value', null, array('label' => 'Значение'))
      ->add('code', null, array('label' => 'Символьный код'));
  }
}
?>
