<?php
namespace Insurance\ContentBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
//use Doctrine\ORM\EntityRepository;

class InsuranceOrderAdmin extends Admin {

  public function configureFormFields(FormMapper $form)
  {
    $form->add('active', null, array('label' => 'Активность', 'required'=>false))
      ->add('status', 'choice', array('choices' => array('W' => 'В ожидании', 'P' => 'Оплачен', 'C'=> 'Подтвержден',
          'label' => 'Статус платежа')))
      ->add('price', null, array('label' => 'Цена'))
      ->add('priceDgo', null, array('label' => 'Цена ДГО', 'required' => false, ))
      ->add('priceNs', null, array('label' => 'Цена НС', 'required' => false, ))
      ->add('user', null, array('label' => 'Пользователь'))
      ->add('company', null, array('label' => 'Страховая'))
      ->add('city', null, array('label' => 'Город'))
      ->add('carModel', null, array('label' => 'Модель авто', 'property' => 'brandCar', ))
      ->add('activeFrom', null, array('label' => 'Активен с', 'years' => range(date('Y')-10, date('Y')+10)))
      ->add('vinCode', null, array('label' => 'VIN код'))
      ->add('carNumber', null, array('label' => 'Номерной знак'))
      ->add('surname', null, array('label' => 'Фамилия'))
      ->add('firstname', null, array('label' => 'Имя'))
      ->add('middlename', null, array('label' => 'Отчество'))
      ->add('documentType', 'choice', array('choices' => array('P' => 'Паспорт', 'D' => 'Водительское удостоверение'),'label' => 'Тип документа'))
      ->add('documentSerie', null, array('label' => 'Серия документа'))
      ->add('documentNumber', null, array('label' => 'Номер документа'))
      ->add('documentAuthority', null, array('label' => 'Кем выдан документ'))
      ->add('documentDate', null, array('label' => 'Дата выдачи документа', 'years' => range(date('Y')-40, date('Y') )))
      ->add('phone', null, array('label' => 'Телефон'))
      ->add('registerCity', null, array('label' => 'Город регистрации', 'property' => 'cityRegion'))
      ->add('registerAddress', null, array('label' => 'Адрес регистрации'))
      ->add('registerBuilding', null, array('label' => 'Дом регистрации'))
      ->add('deliveryCity', null, array('label' => 'Город доставки'))
      ->add('deliveryAddress', null, array('label' => 'Адрес доставки'))
      ->add('deliveryBuilding', 'text', array('label' => 'Дом доставки'))
      ->add('payStatus', null, array('label' => 'Состояние оплаты', 'required' => false, ))
      ->add('payType', null, array('label' => 'Тип оплаты'))
      ->add('orderDate', null, array('label' => 'Дата оформления заказа'))
      ->add('payDate', null, array('label' => 'Дата оплаты'))
      ->add('pdfUrl', null, array('label' => 'Файл договора (PDF)'));
  }

  public function configureListFields(ListMapper $list)
  {
    //$policy = $this->getConfigurationPool()->getContainer()->get('translation');
    $list->addIdentifier('id')
      ->add('status', 'string', array('template' => array('W' => 'В ожидании', 'P' => 'Оплачен', 'C'=> 'Подтвержден',),
        'label' => 'Статус платежа',
          'template' => 'InsuranceContentBundle:Helper:enum_field_list.html.twig',
            ))
      ->add('company', null, array('label' => 'Страховая'))
      ->add('policy', null, array('label' => 'Номер полиса'))
      ->add('priceDgo', null, array('label' => 'Цена ДГО'))
      ->add('priceNs', null, array('label' => 'Цена НС'))
      ->add('city', null, array('label' => 'Город'))
      ->add('carModel', null, array('label' => 'Модель авто', 'property' => 'value'))
      ->add('surname', null, array('label' => 'Фамилия'))
      ->add('firstname', null, array('label' => 'Имя'))
      ->add('middlename', null, array('label' => 'Отчество'))
      ->add('phone', null, array('label' => 'Телефон'))
      ->add('payStatus', null, array('label' => 'Состояние оплаты'))
      ->add('payType', null, array('label' => 'Тип оплаты'));
  }

  public function configureDatagridFilters(DatagridMapper $filter)
  {
    $filter->add('id')
      ->add('status', 'doctrine_orm_string', array('label' => 'Статус'), 'choice', array('choices' => array('W' => 'В ожидании', 'P' => 'Оплачен', 'C'=> 'Подтвержден',),'label'
        => 'Статус платежа'))
      ->add('user', null, array('label' => 'Пользователь'))
      ->add('company', null, array('label' => 'Страховая'))
      ->add('city', null, array('label' => 'Город'))
      ->add('carModel', null, array('label' => 'Модель авто',), null, array('property' => 'value'))
      ->add('surname', null, array('label' => 'Фамилия'))
      ->add('firstname', null, array('label' => 'Имя'))
      ->add('middlename', null, array('label' => 'Отчество'))
      ->add('phone', null, array('label' => 'Телефон'))
      ->add('price', null, array('label' => 'Цена'))
      ->add('priceDgo', null, array('label' => 'Цена ДГО'))
      ->add('priceNs', null, array('label' => 'Цена НС'))
      ->add('payStatus', null, array('label' => 'Состояние оплаты'))
      ->add('orderDate', null, array('label' => 'Дата заказа'))
      ->add('payDate', null, array('label' => 'Дата оплаты'))
      ->add('payType', null, array('label' => 'Тип оплаты'));
  }
  public function configureShowFields(ShowMapper $filter)
  {
    $filter->add('id')
      ->add('status', 'choice', array('choices' => array('W' => 'В ожидании', 'P' => 'Оплачен', 'C'=> 'Подтвержден',),'label' => 'Статус',))
      ->add('user', null, array('label' => 'Пользователь'))
      ->add('company', null, array('label' => 'Страховая'))
      ->add('city', null, array('label' => 'Город'))
      ->add('carModel', null, array('label' => 'Модель авто', 'property' => 'value'))
      ->add('surname', null, array('label' => 'Фамилия'))
      ->add('firstname', null, array('label' => 'Имя'))
      ->add('middlename', null, array('label' => 'Отчество'))
      ->add('phone', null, array('label' => 'Телефон'))
      ->add('price', null, array('label' => 'Цена'))
      ->add('priceDgo', null, array('label' => 'Цена ДГО'))
      ->add('priceNs', null, array('label' => 'Цена НС'))
      ->add('payStatus', null, array('label' => 'Состояние оплаты'))
      ->add('payType', null, array('label' => 'Тип оплаты'));
  }

  public function prePersist($object) {

    $policy = $this->getConfigurationPool()->getContainer()->get('doctrine')
            ->getRepository('InsuranceContentBundle:Policy')
            ->findOneBy(array('status' => 0, 'company' => $object->getCompany()), array('id' => 'ASC'));
    $object->setPolicy($policy);
    $policy->setStatus(1);
    parent::prePersist($object);
  }
}