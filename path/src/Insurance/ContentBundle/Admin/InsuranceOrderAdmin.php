<?php
namespace Insurance\ContentBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
//use Doctrine\ORM\EntityRepository;

class InsuranceOrderAdmin extends Admin {
    protected $translationDomain = 'admin';
  public function configureFormFields(FormMapper $form)
  {
    $form->with('General')
      ->add('active', null, array('label' => 'Активность', 'required'=>false))
      ->add('status', 'choice', array('choices' => array('W' => 'В ожидании', 'P' => 'Обработан', 'C'=> 'Подтвержден',
          'label' => 'Статус заказа')))
      ->add('user', null, array('label' => 'Пользователь'))
      ->add('company', null, array('label' => 'Страховая'))
      //->add('carBrand', null, array('label' => 'Марка авто', 'property' => 'brandCar', ))
      ->add('carModel', null, array('label' => 'Модель авто', 'by_reference' => true/*'property' => 'brandCar',*/ ))
      ->add('activeFrom', null, array('label' => 'Активен с', 'years' => range(date('Y')-10, date('Y')+10)))
      ->add('displacement', null, array('label' => 'Объем двигателя'))
      ->add('carAge', null, array('label' => 'Год выпуска автомобиля'))
      ->add('vinCode', null, array('label' => 'VIN код'))
      ->add('carNumber', null, array('label' => 'Номерной знак'))
      ->add('surname', null, array('label' => 'Фамилия'))
      ->add('firstname', null, array('label' => 'Имя'))
      ->add('middlename', null, array('label' => 'Отчество'))
      ->end()
      ->with('Pay')
      ->add('price', null, array('label' => 'Цена'))
      ->add('priceDgo', null, array('label' => 'Цена ДГО', 'required' => false, ))
      ->add('priceNs', null, array('label' => 'Цена НС', 'required' => false, ))
      ->add('sumDgo', null, array('label' => 'Сумма по ДГО', 'required' => false, ))
      ->add('sumNs', null, array('label' => 'Сумма НС', 'required' => false, ))
      ->add('passengerCount', null, array('label' => 'Количество пассажиров', 'required' => false, ))
      ->add('discount', null, array('label' => 'Скидка', 'required' => false,))
      ->add('payStatus', null, array('label' => 'Состояние оплаты', 'required' => false, ))
      ->add('payType', null, array('label' => 'Тип оплаты'))
      ->add('orderDate', null, array('label' => 'Дата оформления заказа'))
      ->add('payDate', null, array('label' => 'Дата оплаты'))
      //->add('policy', 'sonata_type_model_list', array('label' => 'Полис'))
      ->end()
      ->with('Additional')
      ->add('documentType', 'choice', array('choices' => array('P' => 'Паспорт', 'D' => 'Водительское удостоверение'),'label' => 'Тип документа'))
      ->add('documentSerie', null, array('label' => 'Серия документа'))
      ->add('documentNumber', null, array('label' => 'Номер документа'))
      ->add('documentAuthority', null, array('label' => 'Кем выдан документ'))
      ->add('documentDate', null, array('label' => 'Дата выдачи документа', 'years' => range(date('Y')-40, date('Y') )))
      ->add('phone', null, array('label' => 'Телефон'))
      ->add('city', null, array('label' => 'Город', 'property' => 'cityRegion'))
      ->add('registerCity', null, array('label' => 'Город регистрации', 'property' => 'cityRegion'))
      ->add('registerAddress', null, array('label' => 'Адрес регистрации'))
      ->add('registerBuilding', null, array('label' => 'Дом регистрации'))
      ->add('deliveryCity', null, array('label' => 'Город доставки', 'property' => 'cityRegion'))
      ->add('deliveryAddress', null, array('label' => 'Адрес доставки'))
      ->add('deliveryBuilding', 'text', array('label' => 'Дом доставки'))
      ->add('pdfUrl', null, array('label' => 'Файл договора (PDF)'))
       ->end();

  }

  public function configureListFields(ListMapper $list)
  {
    //$policy = $this->getConfigurationPool()->getContainer()->get('translation');
    $list->addIdentifier('id')
      ->add('status', 'string', array('template' => array('W' => 'В ожидании', 'P' => 'Обработан', 'C'=> 'Подтвержден',),
        'label' => 'Статус заказа',
          'template' => 'InsuranceContentBundle:Helper:enum_field_list.html.twig',
            ))
      //->add('company', null, array('label' => 'Страховая'))
      ->add('policy', null, array('label' => 'Номер полиса'))
      ->add('orderDate', null, array('input_type' => 'date', 'format' => 'd.m.Y', 'label' => 'Дата заказа'))
      ->add('price', null, array('label' => 'Цена'))
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
        ->add('status', 'doctrine_orm_string', array('label' => 'Статус'), 'choice', array('choices' => array('W' => 'В ожидании', 'P' => 'Обработан', 'C'=> 'Подтвержден',),'label'
          => 'Статус заказа'))
        ->add('payStatus', null, array('label' => 'Состояние оплаты'))
        ->add('payType', null, array('label' => 'Тип оплаты'))
        ->add('orderDate', 'doctrine_orm_datetime_range', array('input_type' => 'date', 'date_format' => 'yyyy-MM-dd', 'label' => 'Дата заказа', 'format' => 'yMMMMd'))
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
        ->add('payDate', null, array('label' => 'Дата оплаты'));
  }
  public function configureShowFields(ShowMapper $filter)
  {
    $filter->add('id')
      ->add('status', 'choice', array('choices' => array('W' => 'В ожидании', 'P' => 'Обработан', 'C'=> 'Подтвержден',),'label' => 'Статус заказа',))
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
