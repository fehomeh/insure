<?php

namespace Insurance\ContentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Insurance\ContentBundle\Entity\Region;
use Insurance\ContentBundle\Entity\City;
use Insurance\ContentBundle\Form\FeedbackType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Insurance\ContentBundle\Entity\Feedback;
use Insurance\ContentBundle\Entity\InsuranceOrder;
use Application\Sonata\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Session\Session;
use Insurance\ContentBundle\Helper\PayU;
use Symfony\Component\Validator\Constraints\DateTime;

class DefaultController extends Controller
{
    const DEFAULT_COMPANY_ID = 2;
    public function indexAction($name = 'Stranger')
    {
      //$regions = $this->getDoctrine()->getRepository('InsuranceContentBundle:Region')->findAll();
        //Check for stored calculation and if any redirect to calculator
//        if ($this->getStoredCalculation($this->getRequest())) {
//            return $this->redirect($this->generateUrl('step1'));
//        }
        $carBrands = $this->getDoctrine()->getRepository('InsuranceContentBundle:CarBrand')->findAll();
        $feedbackForm = $this->createForm(new FeedbackType());
        $session = $this->getRequest()->getSession();
        if ($carBrand = $session->get('carBrand')) {
            $carModels = $this->getDoctrine()->getRepository('InsuranceContentBundle:CarModel')->findByBrand($carBrand);
        } else $carModels = array();
        if (!$session->get('timerEnd')) {
            $session->set('timerEnd', date('d M Y H:i:s', time() + $this->container->getParameter('timer')));
        }
        $savedSum = $this->container->getParameter('economy.sum') +
            ($this->container->getParameter('economy.step') * floor(((time() - strtotime($this->container->getParameter('economy.startdate')))/86400)));
        if (strlen($savedSum) > 6) {
            $tmpTime = (int)(((1000000 - $this->container->getParameter('economy.sum'))/$this->container->getParameter('economy.step')) * 86400);
            $savedSum = $this->container->getParameter('economy.sum') +
            ($this->container->getParameter('economy.step') * floor(((time() - $tmpTime - strtotime($this->container->getParameter('economy.startdate')))/86400)));
        }
        return $this->render('InsuranceContentBundle:Default:index.html.twig', array(
          'brands' => $carBrands,
          'carBrand' => $carBrand,
          'models' => $carModels,
          'carModel' => $session->get('carModel'),
          'feedback_form' => $feedbackForm->createView(),
          'callback_form' => $feedbackForm->createView(),
          'timerEnd' => $session->get('timerEnd'),
          'savedSum' => $savedSum,
          ));
    }

    /**
     * AJAX action to get cities list after select some region on page
     * @return JSON encoded list of cities
     */
    public function getCitiesAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $request = $this->getRequest();
            $region_id = $request->get('region_id');
            if ($cities = $this->getDoctrine()->getRepository('InsuranceContentBundle:City')->findBy(
                array('region' => $region_id), array('value' => 'ASC'))) {
            foreach ($cities as $city)
                $city_list[$city->getId()] = $city->getValue();
            } else $city_list = array();
            $response = new Response(json_encode($city_list));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        } else throw $this->createNotFoundException('Wrong request!');
    }

    public function getCarModelsAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $request = $this->getRequest();
            $brand_id = $request->get('brand_id');
            if ($models = $this->getDoctrine()->getRepository('InsuranceContentBundle:CarModel')->findBy(
                array('brand' => $brand_id), array('value' => 'ASC'))) {
            foreach ($models as $model)
                $models_list[$model->getId()] = $model->getValue();
            } else $models_list = array();
            $response = new Response(json_encode($models_list));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        } else throw $this->createNotFoundException('Wrong request!');
    }

    /**
     * Action to save car model and brand into session and redirect user to calculator
     */
    public function goToStepOneAction()
    {
        $session = $this->getRequest()->getSession();
        $request = $this->getRequest();
        $securityContext = $this->container->get('security.context');
        if( $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') ){
    // authenticated REMEMBERED, FULLY will imply REMEMBERED (NON anonymous)
        }
        if ($request->request->get('carBrand') > 0 && $request->request->get('carModel')) {
            $session->set('carBrand', $request->request->get('carBrand'));
            $session->set('carModel', $request->request->get('carModel'));
            return $this->redirect($this->generateUrl('step1'));
        } else {
            return $this->redirect($this->generateUrl('insurance_content_homepage'));
        }
    }

    /**
     * Action renders calculator template and processes it data: saves to session
     * check for errors, calculates prices and redirects user to next page - formalization
     */
    public function calculatorAction(Request $request)
    {
        $session = $this->getRequest()->getSession();
        $this->getStoredCalculation($this->getRequest());
        $calculator = $this->get('insurance.service.calculator');
        $feedbackForm = $this->createForm(new FeedbackType());
        if ($carBrand = $session->get('carBrand')) {
          $models = $this->getDoctrine()->getRepository('InsuranceContentBundle:CarModel')
              ->findByBrand($carBrand);
        } else $models = null;
        if ($registerRegion = $session->get('registerRegion')) {
            $cities = $this->getDoctrine()->getRepository('InsuranceContentBundle:City')->findByRegion($registerRegion);
        } else $cities = null;
        if ($request->getMethod() == 'POST') {
            $error = array();
            $calculator = $this->get('insurance.service.calculator');
            $carBrand = $request->request->get('carBrand');
            $carModel = $request->request->get('hCarModel');
            $carAge = $request->request->get('carAge');
            //$experience = $request->request->get('hExperience');
            $experience = 1;
            $displacement = $request->request->get('hDisplacement');
            $registerRegion = $request->request->get('registerRegion');
            $registerCity = $request->request->get('registerCity');
            $insuranceTerm = $request->request->get('insuranceTerm');
            $passengersCount = $request->request->get('passengersCount');
            $dgoSum = $request->request->get('hDGOSum');
            $nsSum = $request->request->get('hNSSum');
            if ($carBrand > 0) $session->set('carBrand', $carBrand);
                else $error['carBrand'] = 'Не заполнено поле';
            if ($carModel > 0) $session->set('carModel', $carModel);
                else $error['hCarModel'] = 'Не заполнено поле';
            if ($displacement > 0) $session->set('displacement', $displacement);
                else $error['hDisplacement'] = 'Не заполнено поле';
            if ($carAge > 0) $session->set('carAge', $carAge);
                else $error['carAge'] = 'Не заполнено поле';
            if ($registerRegion > 0) $session->set('registerRegion', $registerRegion);
                else $error['registerRegion'] = 'Не заполнено поле';
            if ($registerCity > 0) $session->set('registerCity', $registerCity);
                else $error['registerCity'] = 'Не заполнено поле';
            if ($insuranceTerm > 0) $session->set('insuranceTerm', $insuranceTerm);
                else $error['insuranceTerm'] = 'Не заполнено поле';
            if ($request->request->get('cbDGO') == 'yes' && $dgoSum > 0)
            {
                $session->set('dgoSum', $dgoSum);
                if ($request->request->get('taxiUse') == 1)
                    $session->set('taxiUse', 1);
                else
                    $session->set('taxiUse', 0);
                $session->set('priceDGO', $calculator->calculateDgo(array(
                    'sum' => $dgoSum,
                    'displacement' => $displacement,
                    'experience' => $experience,
                    'term' => $insuranceTerm,
                    'taxi' => $request->request->get('taxiUse')? 1 : -1,
                    'company' => static::DEFAULT_COMPANY_ID,
                    )
                ));
            } else if ($request->request->get('cbDGO') == 'yes')
                $error['dgoSum'] = 'Не заполнено поле';
            if ($request->request->get('cbNS') == 'yes' && $nsSum > 0) {
                $session->set('nsSum', $nsSum);
                $session->set('passengersCount', $passengersCount);
                $session->set('priceNs', $calculator->calculateNs(array(
                    'sum' => $nsSum,
                    'passenger_count' => $passengersCount,
                    'company' => static::DEFAULT_COMPANY_ID,
                    )
                ));
                if ($passengersCount >= 0) $session->set('passengersCount', $passengersCount);
                else $error['passengersCount'] = 'Не заполнено поле';
            } else if ($request->request->get('cbNS') == 'yes')
            {
                if ($passengersCount >= 0)
                    $session->set('passengersCount', $passengersCount);
                else
                    $error['passengersCount'] = 'Не заполнено поле';
                $error['nsSum'] = 'Не заполнено поле';
            }
            $calculator->setCompany(static::DEFAULT_COMPANY_ID)
                ->setRateType('base');
            if ($insuranceTerm > 7) {
                $discountObj = $calculator->getRate(date('Y') - $carAge, 'year');
                if ($discountObj)
                    $discount = $discountObj->getValue();
                else
                    $discount = 1;
            } else
                $discount = 1;
            $session->set('discount', $discount);
            $city = $this->getDoctrine()->getRepository('InsuranceContentBundle:City')->findOneById($registerCity);
            if(!empty($city)) {
            $session->set('price', $calculator->calculateCommon(array(
                'region' => $city->getValue(),
                'displacement' => $displacement,
                'experience' => $experience,
                'term' => $insuranceTerm,
                'year' => $carAge,
                'company' => static::DEFAULT_COMPANY_ID,
            )) * $discount);
            } else $error['registerCity'] = 'Город не найден!';
            if (count($error) == 0) {
                //return $this->redirect($this->generateUrl('step2'));
                $response = new Response(json_encode(array('message' => 'success')));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }
            else {
                $response = new Response(json_encode(array('error' => $error, 'message' => 'error')));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }
        }
        return $this->render('InsuranceContentBundle:Default:step_one.html.twig',array(
            'carBrand' => $session->get('carBrand'),
            'carModel' => $session->get('carModel'),
            'brands' => $this->getDoctrine()->getRepository('InsuranceContentBundle:CarBrand')->findAll(),
            'models' => $models,
            'regions' => $this->getDoctrine()->getRepository('InsuranceContentBundle:Region')->findAll(),
            'cities' => $cities,
            'region' => (isset($registerRegion)?$registerRegion:null),
            'city' => (isset($registerCity)?$registerCity:null),
            'carAgeFrom' => $this->container->getParameter('car.age.from'),
            'carAgeTo' => (int)date('Y'),
            'displacement' => $this->container->getParameter('displacement'),
            'insureTerm' => $this->container->getParameter('insure.period'),
            'dgoSum' => $this->container->getParameter('dgo.sum'),
            'nsSum' => $this->container->getParameter('ns.sum'),
            'passengers' => $this->container->getParameter('passengers'),
            'error' => isset($error)?$error:null,
            'feedback_form' => $feedbackForm->createView(),
            'callback_form' => $feedbackForm->createView(),
        ));
    }

    /**
     * Output registration and policy formalization form also process data from this form
     *
     */
    public function formalizationAction(Request $request)
    {
        $securityContext = $this->container->get('security.context');
        if($securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $session = $this->getRequest()->getSession();
            $feedbackForm = $this->createForm(new FeedbackType());
            if ($request->getMethod() == 'POST') {
                //init error container
                $error = array();
                $activeFrom = $request->request->get('activeFrom');
                $vinCode = $request->request->get('vinCode');
                $carNumber = $request->request->get('carNumber');
                $surname = $request->request->get('surname');
                $firstname = $request->request->get('firstname');
                $middlename = $request->request->get('middlename');
                $birthDate = $request->request->get('birthDate');
                $documentType = $request->request->get('documentType');
                $documentSerie = $request->request->get('documentSerie');
                $documentNumber = $request->request->get('documentNumber');
                $documentAuthority = $request->request->get('documentAuthority');
                $documentDate = $request->request->get('documentDate');
                $documentInn = $request->request->get('documentInn');
                $phone = $request->request->get('phone');
                $region = $request->request->get('region');
                $city = $request->request->get('city');
                $registerAddress = $request->request->get('registerAddress');
                $registerBuilding = $request->request->get('registerBuilding');            //Lets make custom validation for form variables
                $processPersonalData = $request->request->get('processPersonalData');
                if (strlen($activeFrom) === 0) {
                    $error['activeFrom'] = 'Не заполнено поле';
                } elseif (!preg_match('#[0-9]{1,2}\.[0-9]{1,2}\.[0-9]{4}#', $activeFrom)) {
                    $error['activeFrom'] = 'Неправильный формат даты';
                } else {
                    $session->set('activeFrom', $activeFrom);
                }

                if (strlen($vinCode) === 0) {
                    $error['vinCode'] = 'Не заполнено поле';
                } else {
                    $session->set('vinCode', $vinCode);
                }

                if (strlen($carNumber) === 0) {
                    $error['carNumber'] = 'Не заполнено поле';
                } else {
                    $session->set('carNumber', $carNumber);
                }

                if (strlen($surname) === 0) {
                    $error['surname'] = 'Не заполнено поле';
                } else {
                    $session->set('surname', $surname);
                }

                if (strlen($firstname) === 0) {
                    $error['firstname'] = 'Не заполнено поле';
                } else {
                    $session->set('firstname', $firstname);
                }

                if (strlen($middlename) === 0) {
                    $error['middlename'] = 'Не заполнено поле';
                } else {
                    $session->set('middlename', $middlename);
                }

                if (strlen($birthDate) === 0) {
                    $error['birthDate'] = 'Не заполнено поле';
                } elseif (!preg_match('#[0-9]{1,2}\.[0-9]{1,2}\.[0-9]{4}#', $birthDate)) {
                    $error['birthDate'] = 'Неправильный формат даты';
                } else {
                    $session->set('birthDate', $birthDate);
                }

                if (strlen($documentType) === 0) {
                    $error['documentType'] = 'Не заполнено поле';
                } elseif ($documentType == 'P' && $documentType == 'D') {
                    $error['documentType'] = 'Неверный тип документа';
                } else {
                    $session->set('documentType', $documentType);
                }

                if (strlen($documentSerie) === 0) {
                    $error['documentSerie'] = 'Не заполнено поле';
                } else {
                    $session->set('documentSerie', $documentSerie);
                }

                if (strlen($documentNumber) === 0) {
                    $error['documentNumber'] = 'Не заполнено поле';
                } else {
                    $session->set('documentNumber', $documentNumber);
                }

                if (strlen($documentAuthority) === 0) {
                    $error['documentAuthority'] = 'Не заполнено поле';
                } else {
                    $session->set('documentAuthority', $documentAuthority);
                }

                if (strlen($documentDate) === 0) {
                    $error['documentDate'] = 'Не заполнено поле';
                } elseif (!preg_match('#[0-9]{1,2}\.[0-9]{1,2}\.[0-9]{4}#', $documentDate)) {
                    $error['documentDate'] = 'Неправильный формат даты';
                } else {
                    $session->set('documentDate', $documentDate);
                }

                if (strlen($documentInn) === 0) {
                    $error['documentInn'] = 'Не заполнено поле';
                } else {
                    $session->set('documentInn', $documentInn);
                }

                if ($region > 0) {
                    $session->set('phone', $phone);
                } else {
                    $error['phone'] = 'Не заполнено поле';
                }

                if ($region > 0) {
                    $session->set('region', $region);
                } else {
                    $error['region'] = 'Не заполнено поле';
                }

                if ($city > 0) {
                    $session->set('city', $city);
                } else {
                    $error['city'] = 'Не заполнено поле';
                }

                if (strlen($registerAddress) === 0) {
                    $error['registerAddress'] = 'Не заполнено поле';
                } else {
                    $session->set('registerAddress', $registerAddress);
                }

                if (strlen($registerBuilding) === 0) {
                    $error['registerBuilding'] = 'Не заполнено поле';
                } else {
                    $session->set('registerBuilding', $registerBuilding);
                }
                if ($processPersonalData == 'Y')
                if (count($error) == 0) return $this->redirect($this->generateUrl('step3'));
            }
            $carBrand = $this->getDoctrine()->getRepository('InsuranceContentBundle:CarBrand')->findOneById($session->get('carBrand'));
            $carModel = $this->getDoctrine()->getRepository('InsuranceContentBundle:CarModel')->findOneById($session->get('carModel'));
            $usr = $this->get('security.context')->getToken()->getUser();
            $registerPersons = $this->getDoctrine()->getRepository('InsuranceContentBundle:InsuranceOrder')->findBy(
                array(
                    'user' => $usr->getId(),
                ),
                array(
                    'id' => 'desc',
                ));
            if (is_null($carBrand) || is_null($carModel)) {
                return $this->redirect($this->generateUrl('step1'));
            }
            return $this->render('InsuranceContentBundle:Default:step_two.html.twig', array(
                'carModel' => $carModel->getValue(),
                'carBrand' => $carBrand->getValue(),
                'regions' => $this->getDoctrine()->getRepository('InsuranceContentBundle:Region')->findAll(),
                'cities' => (isset($region))?$this->getDoctrine()->getRepository('InsuranceContentBundle:City')->findByRegion($region):null,
                'errors' => isset($error) ? $error : null,
                'registerPersons' => $registerPersons,
                'feedback_form' => $feedbackForm->createView(),
                'callback_form' => $feedbackForm->createView(),
                )
            );
        } else return $this->redirect($this->generateUrl('step1'));
    }


    public function deliveryAction(Request $request)
    {
        $feedbackForm = $this->createForm(new FeedbackType());
        //If user is authorized and URL has hash than get data from database and push it to session
        $securityContext = $this->container->get('security.context');
        $hash = $request->query->get('hash', null);
        $session = $request->getSession();
        if($securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') && !is_null($hash)) {
            $savedOrder = $this->getDoctrine()->getRepository('InsuranceContentBundle:InsuranceOrder')->findOneByHash($hash);
            $session->set('carBrand', $savedOrder->getCarModel()->getBrand()->getId());
            $session->set('carModel', $savedOrder->getCarModel()->getId());
            $session->set('displacement', $savedOrder->getDisplacement());
            $session->set('carAge', $savedOrder->getCarAge());
            $session->set('registerRegion', $savedOrder->getRegisterCity()->getRegion()->getId());
            $session->set('registerCity', $savedOrder->getRegisterCity()->getId());
            $session->set('insuranceTerm', $savedOrder->getInsuranceTerm());
            $session->set('dgoSum', $savedOrder->getSumDgo());
            $session->set('priceDGO', $savedOrder->getPriceDgo());
            $session->set('taxiUse', $savedOrder->getTaxiUse());
            $session->set('nsSum', $savedOrder->getSumNs());
            $session->set('passengersCount', $savedOrder->getPassengerCount());
            $session->set('priceNs', $savedOrder->getPriceNs());
            $session->set('discount', $savedOrder->getDiscount());
            $session->set('price', $savedOrder->getPrice());

            $session->set('activeFrom', $savedOrder->getActiveFrom()->format('d.m.Y'));
            $session->set('vinCode', $savedOrder->getVinCode());
            $session->set('carNumber', $savedOrder->getCarNumber());
            $session->set('surname', $savedOrder->getSurname());
            $session->set('firstname', $savedOrder->getFirstname());
            $session->set('middlename', $savedOrder->getMiddlename());
            $session->set('birthDate', $savedOrder->getBirthDate()->format('d.m.Y'));
            $session->set('documentType', $savedOrder->getDocumentType());
            $session->set('documentSerie', $savedOrder->getDocumentSerie());
            $session->set('documentNumber', $savedOrder->getDocumentNumber());
            $session->set('documentAuthority', $savedOrder->getDocumentAuthority());
            $session->set('documentDate', $savedOrder->getDocumentDate()->format('d.m.Y'));
            $session->set('documentInn', $savedOrder->getDocumentInn());
            $session->set('phone', $savedOrder->getPhone());
            $session->set('region', $savedOrder->getCity()->getRegion()->getId());
            $session->set('city', $savedOrder->getCity()->getId());
            $session->set('registerAddress', $savedOrder->getRegisterAddress());
            $session->set('registerBuilding', $savedOrder->getRegisterBuilding());

            $session->set('deliveryRegion', $savedOrder->getDeliveryCity()->getRegion()->getId());
            $session->set('deliveryCity', $savedOrder->getDeliveryCity()->getId());
            $session->set('deliveryAddress', $savedOrder->getDeliveryAddress());
            $session->set('deliveryBuilding', $savedOrder->getDeliveryBuilding());
            $session->set('phone', $savedOrder->getPhone());
            $session->set('payType', $savedOrder->getPayType());
            $session->set('activity', $savedOrder->getActive());
            $needAuth = false;
        } elseif (!is_null($hash)) $needAuth = true;
            else $needAuth = false;
        $region = $this->getDoctrine()->getRepository('InsuranceContentBundle:Region')->findOneById($session->get('region'));
        $regionId = $session->get('region');
        $cityId = $session->get('city');
        //If user visit this page right after register page than fill delivery address from register address stored in session
        $deliveryAddress = $session->get('registerAddress');
        $deliveryBuilding = $session->get('registerBuilding');
        if($request->getMethod() == 'POST') {
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $errors = array();
            $deliveryRegion = $request->request->get('deliveryRegion');
            $deliveryCity = $request->request->get('deliveryCity');
            $deliveryAddress = $request->request->get('deliveryAddress');
            $deliveryBuilding = $request->request->get('deliveryBuilding');
            $phone = $request->request->get('phone');
            $payType = $request->request->get('payType');
            $activity = $request->request->get('activity');
            if ($deliveryRegion > 0 && $deliveryCity > 0 && strlen($deliveryAddress) > 0 && strlen($deliveryBuilding) && strlen($phone) > 0 && strlen($payType) > 0 && strlen($payType) > 0 && ($activity === '1' || $activity === '0')) {
                $session->set('deliveryRegion', $deliveryRegion);
                $session->set('deliveryCity', $deliveryCity);
                $session->set('deliveryAddress', $deliveryAddress);
                $session->set('deliveryBuilding', $deliveryBuilding);
                $session->set('phone', $phone);
                $session->set('payType', $payType);
                $session->set('activity', $activity);
                $user = $this->getUser();
                //Here we store all data to database
                $order = new InsuranceOrder();
                if ($activity === '1') $order->setActive(1);
                elseif ($activity === '0') {
                    //Means that user decided to hold order for a while
                    $order->setActive(0);
                    $order->setHash(sha1(microtime()));
                }
                $order->setStatus('W'); //W - waiting, P  - paid, C - confirmed
                $order->setPayStatus(0);
                $order->setOrderDate(new \DateTime('now'));
                try {
                    $user = $this->get('security.context')->getToken()->getUser();
                    //if (is_null($user)) $user = $this->getDoctrine()->getRepository('ApplicationSonataUserBundle:User')->findOneById($user->getId());
                    $order->setUser($user);
                } catch (\Doctrine\ORM\EntityNotFoundException $e) {
                    $errors['user'] = "Не найден пользователь";
                }

                try {
                    $company = $this->getDoctrine()->getRepository('InsuranceContentBundle:InsuranceCompany')->findOneById(static::DEFAULT_COMPANY_ID);
                    $order->setCompany($company);
                } catch (\Doctrine\ORM\EntityNotFoundException $e) {
                    $errors['company'] = "Не найдена компания";
                }

                try {
                $carModel = $this->getDoctrine()->getRepository('InsuranceContentBundle:CarModel')->findOneById($session->get('carModel'));
                } catch (\Doctrine\ORM\EntityNotFoundException $e) {
                    $errors['carModel'] = "Не найдена компания";
                }
                $order->setCarModel($carModel);

                $order->setDisplacement($session->get('displacement'));

                $order->setCarAge($session->get('carAge'));

                try {
                    $registerCity = $this->getDoctrine()->getRepository('InsuranceContentBundle:City')->findOneById($session->get('registerCity'));
                    $order->setRegisterCity($registerCity);
                } catch (\Doctrine\ORM\EntityNotFoundException $e) {
                    $errors['registerCity'] = "Не найден город регистрации ТС";
                }

                $order->setInsuranceTerm($session->get('insuranceTerm'));

                $order->setTaxiUse(($session->get('taxiUse') == '1' ? 1 : 0));

                $order->setSumDgo($session->get('dgoSum'));

                $order->setPriceDgo($session->get('priceDGO'));

                $order->setSumNs($session->get('nsSum'));

                $order->setPassengerCount($session->get('passengerCount'));

                $order->setPriceNs($session->get('priceNs'));

                $order->setDiscount($session->get('discount'));

                $order->setPrice($session->get('price'));

                $order->setTotalPrice($session->get('price') + $session->get('priceDGO') + $session->get('priceNs'));

                $order->setActiveFrom(new \DateTime($session->get('activeFrom')));

                $order->setVinCode($session->get('vinCode'));

                $order->setCarNumber($session->get('carNumber'));

                $order->setSurname($session->get('surname'));

                $order->setFirstname($session->get('firstname'));

                $order->setMiddlename($session->get('middlename'));

                $order->setBirthDate(new \DateTime($session->get('birthDate')));

                $order->setDocumentType($session->get('documentType'));

                $order->setDocumentSerie($session->get('documentSerie'));

                $order->setDocumentNumber($session->get('documentNumber'));

                $order->setDocumentAuthority($session->get('documentAuthority'));

                $order->setDocumentDate(new \DateTime($session->get('documentDate')));

                $order->setDocumentInn($session->get('documentInn'));

                $order->setPhone($session->get('phone'));

                try {
                    $city = $this->getDoctrine()->getRepository('InsuranceContentBundle:City')->findOneById($session->get('city'));
                    $order->setCity($city);
                } catch (\Doctrine\ORM\EntityNotFoundException $e) {
                    $errors['city'] = "Не найден город регистрации документа";
                }

                $order->setRegisterAddress($session->get('registerAddress'));

                $order->setRegisterBuilding($session->get('registerBuilding'));

                try {
                    $deliveryCity = $this->getDoctrine()->getRepository('InsuranceContentBundle:City')->findOneById($session->get('deliveryCity'));
                    $order->setDeliveryCity($deliveryCity);
                } catch (\Doctrine\ORM\EntityNotFoundException $e) {
                    $errors['city'] = "Не найден город доставки полиса";
                }

                $order->setDeliveryAddress($session->get('deliveryAddress'));

                $order->setDeliveryBuilding($session->get('deliveryBuilding'));

                $order->setPayType($payType);

                $policy = $this->getDoctrine()->getRepository('InsuranceContentBundle:Policy')->findOneById($session->get('policy'));
                if ($policy) {
                    $policy->setStatus(1);
                    $order->setPolicy($policy);
                }
                if (count($errors) == 0) {
                    try {
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($order);
                        $em->flush();
                        if ($activity == '1')
                            $session->set('orderState', 'success');
                        elseif ($activity == '0')
                            $session->set('orderState', 'delayed');
                        $session->remove('policy');
                        $this->clearSessionData($session);
                        $session->set('orderId', $order->getId());
                        switch($payType) {
                            case 'cash':
                                $this->sendNotification($order);
                                $session->set('payType', 'cash');
                                return $response->setContent(json_encode(array('message' => 'redirect', 'url' => $this->generateUrl('finish'))));
                            case 'terminal':
                                $this->sendNotification($order);
                                $session->set('payType', 'terminal');
                                return $response->setContent(json_encode(array('message' => 'redirect', $this->generateUrl('finish'))));
                            case 'plastic':
                            case 'privat_card':
                                $session->set('payType', 'plastic');
                                $response->setContent(json_encode(array('message' => 'redirect', 'url' => $this->generateUrl('pay_redirect'))));
                                return $response;
                            case 'privat24':
                                $session->set('payType', 'privat24');
                                $response->setContent(json_encode(array('message' => 'redirect', 'url' => $this->generateUrl('pay_redirect'))));
                                return $response;
                        }
                    } catch (\Exception $e) {
                        $errors['message'] = $e->getMessage();
                    }
                }
            } else $errors['message'] = 'Все поля обязательны к заполнению';
            //if (count($errors) > 0) return $response->setContent(json_encode(array('message' => 'redirect', 'url' => $this->generateUrl('step3'))));
            if (count($errors) > 0) return $response->setContent(json_encode(array('message' => 'error', 'error' => $errors['message'])));
            }

        $city = $this->getDoctrine()->getRepository('InsuranceContentBundle:City')->findOneById($session->get('city'));
        if ($session->get('policy') != null) {
            $policy = $this->getDoctrine()->getRepository('InsuranceContentBundle:Policy')->findOneById($session->get('policy'));
        } elseif (!isset($policy)) {
            $policy = $this->getDoctrine()->getRepository('InsuranceContentBundle:Policy')->findOneBy(
                array(
                    'status' => 0,
                    'company' => static::DEFAULT_COMPANY_ID,
                ),
                array('id' => 'ASC')
                );
            if ($policy) {
                $session->set('policy', $policy->getId());
                $policy->setStatus(2);
                try {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($policy);
                    $em->flush();
                } catch (\Exception $e) {
                    $errors['message'] = $e->getMessage();
                }
            }
        }
        if ((is_null($region) || is_null($city)) && is_null($hash)) return $this->redirect($this->generateUrl('step2'));
        return $this->render('InsuranceContentBundle:Default:step_three.html.twig', array(
            'regions' => $this->getDoctrine()->getRepository('InsuranceContentBundle:Region')->findAll(),
            'cities' => (!is_null($regionId)?$this->getDoctrine()->getRepository('InsuranceContentBundle:City')->findByRegion($regionId):null),
            'region' => $region,
            'city' => $city,
            'deliveryAddress' => $deliveryAddress,
            'deliveryBuilding' => $deliveryBuilding,
            'errors' => null,
            'feedback_form' => $feedbackForm->createView(),
            'callback_form' => $feedbackForm->createView(),
            'policy' => $policy,
            'showLogin' => $needAuth,
        ));
    }
    /**
     * Creates a new Feedback entity.
     *
     */
    public function createFeedbackAction(Request $request)
    {
        $entity  = new Feedback();
        $form = $this->createForm(new FeedbackType(), $entity);
        $form->bind($request);
        try {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            $resp = new Response(json_encode(array('message' => 'success')));
            $resp->headers->set('Content-Type', 'application/json');
            return $resp;
        } catch (\Exception $e) {
            $response = new Response(json_encode(array('error' => $e->getMessage(), 'message' => 'error')));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
    }

    /**
     * Function processes discount using user input data (car age)
     * @return JSON object with "discount" param
     */
    public function getDiscountAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $carAge = $request->request->get('carAge');
            $insuranceTerm = $request->request->get('insuranceTerm', 0);
            if ($insuranceTerm > 7) {
                $calculator = $this->get('insurance.service.calculator');
                $calculator->setCompany(static::DEFAULT_COMPANY_ID)
                    ->setRateType('base');
                $discount = $calculator->getRate(date('Y') - $carAge, 'year');
                if ($discount !== null) {
                    $respString = json_encode(array('discount' => $discount->getValue()));
                } else $respString = json_encode(array('discount' => 1));
            } else
                $respString = json_encode(array('discount' => 1));
            $response = new Response($respString);
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        } else throw $this->createNotFoundException('Wrong request!');
    }

    /**
     * Calculate general insurance price
     * @return JSON object with "price" key
     */
    public function calculateInsuranceAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $calculator = $this->get('insurance.service.calculator');
            $registerCity = $request->request->get('registerCity');
            $displacement = $request->request->get('hDisplacement');
            $experience = $request->request->get('hExperience');
            $insuranceTerm = $request->request->get('insuranceTerm');
            if ($registerCity > 0 && $displacement > 0 && $experience > 0 &&
            $insuranceTerm > 0) {
                $city = $this->getDoctrine()->getRepository('InsuranceContentBundle:City')->findOneById($registerCity);
                $price = $calculator->calculateCommon(array(
                    'region' => $city->getValue(),
                    'displacement' => $displacement,
                    'experience' => $experience,
                    'term' => $insuranceTerm,
                    'company' => static::DEFAULT_COMPANY_ID,
                    )
                );
            }
            else $price = 0;
            $response = new Response(json_encode(array('price' => $price)));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        } else throw $this->createNotFoundException('Wrong request!');
    }

    /**
     * Calculate price of additional civil responsibility
     * @return JSON object with "priceDgo" key
     */
    public function calculateDgoAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $calculator = $this->get('insurance.service.calculator');
            $dgoSum = $request->request->get('hDGOSum');
            $displacement = $request->request->get('hDisplacement');
            $experience = $request->request->get('hExperience');
            $insuranceTerm = $request->request->get('insuranceTerm');
            if ($dgoSum > 0 && $displacement > 0 && $insuranceTerm > 0 && $experience > 0)
                $priceDgo = $calculator->calculateDgo(array(
                    'sum' => $dgoSum,
                    'displacement' => $displacement,
                    'experience' => $experience,
                    'term' => $insuranceTerm,
                    'taxi' => $request->request->get('taxiUse')? 1 : -1,
                    'company' => static::DEFAULT_COMPANY_ID,
                    )
                );
            else $priceDgo = 0;
            $response = new Response(json_encode(array('priceDgo' => $priceDgo)));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        } else throw $this->createNotFoundException('Wrong request!');
    }

    /**
     * Calculate casualty insurance
     * @return JSON object with "priceNs" key
     */
    public function calculateNsAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $calculator = $this->get('insurance.service.calculator');
            $nsSum = $request->request->get('hNSSum');
            $passengersCount = $request->request->get('passengersCount');
            if ($nsSum > 0)
                $priceNs = $calculator->calculateNs(array(
                    'sum' => $nsSum,
                    'passenger_count' => $passengersCount,
                    'company' => static::DEFAULT_COMPANY_ID,
                    )
                );
            else $priceNs = 0;
            $response = new Response(json_encode(array('priceNs' => $priceNs)));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        } else throw $this->createNotFoundException('Wrong request!');
    }

    public function saveCalculationAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            /*$carBrand = $request->request->get('carBrand');
            $carModel = $request->request->get('hCarModel');
            $carAge = $request->request->get('carAge');
            $displacement = $request->request->get('hDisplacement');
            $registerRegion = $request->request->get('registerRegion');
            $registerCity = $request->request->get('registerCity');
            $insuranceTerm = $request->request->get('insuranceTerm');
            if ($request->request->get('cbDGO') == 'yes') {
                $dgoSum = $request->request->get('hDGOSum');
                $taxiUse = ($request->request->get('taxiUse') == '1' ? 1 : null);
            }
            if ($request->request->get('cbNS') == 'yes') {
                $nsSum = $request->request->get('hNSSum');
                $passengersCount = $request->request->get('passengersCount');
            }*/
            //Send email to user to remember about saved calculation
            $from = $this->container->getParameter('email.send.from');
            $emailName = $this->container->getParameter('email.name');
            $siteName = $this->container->getParameter('site.name');
            $siteDomain = $this->container->getParameter('site.domain');
            $contactEmail = $this->container->getParameter('contact.email');
            $contactPhone = $this->container->getParameter('contact.phone');
            $user = $this->get('security.context')->getToken()->getUser();
            $to = $user->getEmail();
            $message = \Swift_Message::newInstance()
                ->setSubject(strtoupper($siteDomain) . ': Ваш заказ принят!')
                ->setFrom(array($from => $emailName))
                ->setTo($to)
                ->setBody(
                      $this->render(
                          'InsuranceContentBundle:Notifications:savedCalculationNotification.html.twig',
                          array(
                          'siteName' => $siteName,
                          'siteDomain' => $siteDomain,
                          'contactEmail' => $contactEmail,
                          'contactPhone' => $contactPhone,
                          )
                  ),
                  'text/html'
            );
          $this->container->get('mailer')->send($message);
            $cookieData = array(
                'carBrand' => $request->request->get('carBrand'),
                'carModel' => $request->request->get('hCarModel'),
                'carAge' => $request->request->get('carAge'),
                'displacement' => $request->request->get('hDisplacement'),
                'registerRegion' => $request->request->get('registerRegion'),
                'registerCity' => $request->request->get('registerCity'),
                'insuranceTerm' => $request->request->get('insuranceTerm'),
            );
            if ($request->request->get('cbDGO') == 'yes') {
                $cookieData['dgoSum'] = $request->request->get('hDGOSum');
                if ($request->request->get('taxiUse') == '1') $cookieData['taxiUse'] = '1';
            }
            if ($request->request->get('cbNS') == 'yes') {
                $cookieData['nsSum'] = $request->request->get('hNSSum');
                $cookieData['passengersCount'] = $request->request->get('passengersCount');
            }
            $calculationCookie = new Cookie('sc', serialize($cookieData), strtotime('+1 month'));
            $response = new Response(json_encode(array('message' => 'success')));
            $response->headers->set('Content-Type', 'application/json');
            $response->headers->setCookie($calculationCookie);
            return $response;
        }
    }

    /**
     * Check for stored calculation cookie and restores data to session
     * @param \Symfony\Component\HttpFoundation\Request $request Symfony's request object
     * @return bool If cookie is present and session data restored without errors returns true
     */
    public function getStoredCalculation(\Symfony\Component\HttpFoundation\Request $request)
    {
        if ($calculationCookie = $request->cookies->get('sc')) {
            if ($calculationCookie = unserialize($calculationCookie)){
                $session = $request->getSession();
                //$session->start();
                foreach ($calculationCookie as $k => $v) {
                    $session->set($k, $v);
                }
                return true;
            } else return false;
        } else
            return false;
    }

    public function clearStoredDataAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $timerEnd = $request->getSession()->get('timerEnd');
            $request->getSession()->clear();
            if (!empty($timerEnd)) $request->getSession()->set('timerEnd', $timerEnd);
            $response = new Response();
            $response->headers->clearCookie('sc');
            return $response;
        }
    }

    public function finishAction(Request $request)
    {
        $feedbackForm = $this->createForm(new FeedbackType());
        $orderId = $request->getSession()->get('orderId');
        $request->getSession()->remove('orderId');
        if ($request->getSession()->get('orderState') == 'success' && ($request->getSession()->get('payType') == 'cash' || $request->getSession()->get('payType') == 'terminal') && ($orderId > 0)) {
            //$request->getSession()->clear();
            $response = new Response();
            $response->headers->clearCookie('sc');
            $message = '<span class="success"></span><h3>Ваш заказ принят!</h3><p>Спасибо за то, что воспользовались нашим сервисом! Наш менеджер свяжется с Вами в ближайшее время для уточнения деталей доставки.</p>';
        } elseif ($request->getSession()->get('orderState') == 'delayed' && ($request->getSession()->get('payType') == 'cash' || $request->getSession()->get('payType') == 'terminal')) {
            //$request->getSession()->clear();
            $response = new Response();
            $response->headers->clearCookie('sc');
            $message = '<span class="success"></span><h3>Ваш заказ сохранен!</h3><p>На Ваш электронный адрес выслано письмо со ссылкой для завершения заказа.</p> <p>Спасибо за то, что воспользовались нашим сервисом!</p>';
        } else return $this->redirect($this->generateUrl('homepage'));
        return $this->render('InsuranceContentBundle:Default:finish.html.twig', array(
            'feedback_form' => $feedbackForm->createView(),
            'callback_form' => $feedbackForm->createView(),
            'message' => $message,
            'orderId' => $orderId,
        ));
    }

    public function aboutAction()
    {
        $feedbackForm = $this->createForm(new FeedbackType());
        return $this->render('InsuranceContentBundle:Default:about.html.twig', array(
            'feedback_form' => $feedbackForm->createView(),
            'callback_form' => $feedbackForm->createView(),
        ));
    }

    public function contactsAction()
    {
        $feedbackForm = $this->createForm(new FeedbackType());
        return $this->render('InsuranceContentBundle:Default:contacts.html.twig', array(
            'feedback_form' => $feedbackForm->createView(),
            'callback_form' => $feedbackForm->createView(),
        ));
    }
    
    public function faqAction()
    {
        $feedbackForm = $this->createForm(new FeedbackType());
        return $this->render('InsuranceContentBundle:Default:faq.html.twig', array(
            'feedback_form' => $feedbackForm->createView(),
            'callback_form' => $feedbackForm->createView(),
        ));
    }
    public function osagoAction()
    {
        $feedbackForm = $this->createForm(new FeedbackType());
        return $this->render('InsuranceContentBundle:Default:osago-info.html.twig', array(
            'feedback_form' => $feedbackForm->createView(),
            'callback_form' => $feedbackForm->createView(),
        ));
    }
    public function eventAction()
    {
        $feedbackForm = $this->createForm(new FeedbackType());
        return $this->render('InsuranceContentBundle:Default:event-info.html.twig', array(
            'feedback_form' => $feedbackForm->createView(),
            'callback_form' => $feedbackForm->createView(),
        ));
    }
    public function partnerAction()
    {
        $feedbackForm = $this->createForm(new FeedbackType());
        return $this->render('InsuranceContentBundle:Default:partner.html.twig', array(
            'feedback_form' => $feedbackForm->createView(),
            'callback_form' => $feedbackForm->createView(),
        ));
    }
    public function privacypolicyAction()
    {
        $feedbackForm = $this->createForm(new FeedbackType());
        return $this->render('InsuranceContentBundle:Default:privacypolicy.html.twig', array(
            'feedback_form' => $feedbackForm->createView(),
            'callback_form' => $feedbackForm->createView(),
        ));
    }   
    public function clearSessionData($session)
    {
        $session->remove('carBrand');
        $session->remove('carModel');
        $session->remove('displacement');
        $session->remove('carAge');
        $session->remove('registerRegion');
        $session->remove('registerCity');
        $session->remove('insuranceTerm');
        $session->remove('dgoSum');
        $session->remove('priceDGO');
        $session->remove('taxiUse');
        $session->remove('nsSum');
        $session->remove('passengersCount');
        $session->remove('priceNs');
        $session->remove('discount');
        $session->remove('price');

        $session->remove('activeFrom');
        $session->remove('vinCode');
        $session->remove('carNumber');
        $session->remove('surname');
        $session->remove('firstname');
        $session->remove('middlename');
        $session->remove('birthDate');
        $session->remove('documentType');
        $session->remove('documentSerie');
        $session->remove('documentNumber');
        $session->remove('documentAuthority');
        $session->remove('documentDate');
        $session->remove('documentInn');
        $session->remove('phone');
        $session->remove('region');
        $session->remove('city');
        $session->remove('registerAddress');
        $session->remove('registerBuilding');

        $session->remove('deliveryRegion');
        $session->remove('deliveryCity');
        $session->remove('deliveryAddress');
        $session->remove('deliveryBuilding');
        $session->remove('phone');
        $session->remove('payType');
        $session->remove('activity');
        $session->remove('policy');
    }

    public function generatePDFPolicy($orderId)
    {
        $tcPdf = new \TCPDF();
        $request = $this->get('request');
        $router = $this->get('router');
        $doctrine = $this->get('doctrine');
        try {
            $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,strpos( $_SERVER["SERVER_PROTOCOL"],'/'))).'://';
            $policyHTML = file_get_contents($protocol . $request->server->get('HTTP_HOST') . $router->generate('generate_html_policy', array('orderId' => $orderId)));
            error_reporting(E_ERROR);
            $tcPdf->SetFont('dejavusans', '', 10);
            $tcPdf->setImageScale(1.53);
            $tcPdf->AddPage();
            $tcPdf->setJPEGQuality(98);
            $tcPdf->Image('bundles/insurancecontent/images/our_logo.jpg', '', '', '', '', 'JPG', 'http://polismarket.com.ua', 'L', false, 150, '', false, false, 0, false, false, false);
            $tcPdf->Image('bundles/insurancecontent/images/bbs_logo.jpg', '', '', '', '', 'JPG', 'http://polismarket.com.ua', 'R', false, 150, '', false, false, 0, false, false, false);
            $tcPdf->writeHTML($policyHTML);
            $fileName = sha1(microtime());
            $file = $request->server->get('DOCUMENT_ROOT') . '/pdf/' . $fileName . '.pdf';
            $tcPdf->Output($file, 'F');
            $order = $this->get('doctrine')->getRepository('InsuranceContentBundle:InsuranceOrder')->findOneById($orderId);
            $httpFile = $protocol . $request->server->get('HTTP_HOST') . '/pdf/' . $fileName . '.pdf';
            $order->setPdfUrl($httpFile);
            $em = $doctrine->getEntityManager();
            $em->persist($order);
            $em->flush();
        } catch (Exception $e) {
            die($e->getMessage());
            return false;
        }
        return $file;
    }

    public function sendNotification(InsuranceOrder $entity)
    {
        $from = $this->container->getParameter('email.send.from');
        $emailName = $this->container->getParameter('email.name');
        $siteName = $this->container->getParameter('site.name');
        $siteDomain = $this->container->getParameter('site.domain');
        $contactEmail = $this->container->getParameter('contact.email');
        $contactPhone = $this->container->getParameter('contact.phone');
        if ($entity->getPayStatus() == 0 && ($entity->getPayType() == 'terminal' || $entity->getPayType() == 'cash') && $entity->getActive() == 1) {
            //If cash (terminal) payment processed but isn't payed than send notification about unpayed order with atteched electronical version of policy
            $to = $entity->getUser()->getEmail();
            $message = \Swift_Message::newInstance()
                ->setSubject(strtoupper($siteDomain) . ': Ваш заказ принят!')
                ->setFrom(array($from => $emailName))
                ->setTo($to)
                ->setBody(
                    $this->get('templating')->render(
                        'InsuranceContentBundle:Notifications:unpayedOrderNotification.html.twig',
                        array(
                            'order' => $entity,
                            'siteName' => $siteName,
                            'siteDomain' => $siteDomain,
                            'contactEmail' => $contactEmail,
                            'contactPhone' => $contactPhone,
                        )
                    ),
                    'text/html'
                );
            if ($pdfFile = $this->generatePDFPolicy($entity->getId())) {
                $message->attach(\Swift_Attachment::fromPath($pdfFile)->setContentType('application/pdf')->setFilename('Полис ОСАГО.pdf'));
            }
            $this->get('mailer')->send($message);
        } /*elseif ($entity->getPayType() != 'cash' && $entity->getPayStatus() == 1) {
            //After payment succesfully verified send message to user with attached electronical policy version and order details
            $to = $entity->getUser()->getEmail();
            $message = \Swift_Message::newInstance()
                ->setSubject(strtoupper($siteDomain) . ': Оплата за Ваш заказ получена!')
                ->setFrom(array($from => $emailName))
                ->setTo($to)
                ->setBody(
                    $this->get('templating')->render(
                        'InsuranceContentBundle:Notifications:payedOrderNotification.html.twig',
                        array(
                            'order' => $entity,
                            'siteName' => $siteName,
                            'siteDomain' => $siteDomain,
                            'contactEmail' => $contactEmail,
                            'contactPhone' => $contactPhone,
                        )
                    ),
                    'text/html'
                );
            if ($pdfFile = $this->generatePDFPolicy($entity->getId())) {
                $message->attach(\Swift_Attachment::fromPath($pdfFile)->setContentType('application/pdf')->setFilename('Полис ОСАГО.pdf'));
            }
            $this->get('mailer')->send($message);
        }*/ elseif ($entity->getActive() == 0 && $entity->getPayStatus() == 0 && strlen($entity->getHash()) == 40) {
            //Send notification to user that he has stored order without confirmation
            $to = $entity->getUser()->getEmail();
            $message = \Swift_Message::newInstance()
                ->setSubject(strtoupper($siteDomain) . ': Ваш заказ ожидает подтверждения...')
                ->setFrom(array($from => $emailName))
                ->setTo($to)
                ->setBody(
                    $this->get('templating')->render(
                        'InsuranceContentBundle:Notifications:delayedOrderNotification.html.twig',
                        array(
                            'order' => $entity,
                            'siteName' => $siteName,
                            'siteDomain' => $siteDomain,
                            'contactEmail' => $contactEmail,
                            'contactPhone' => $contactPhone,
                        )
                    ),
                    'text/html'
                );
            if ($pdfFile = $this->generatePDFPolicy($entity->getId())) {
                $message->attach(\Swift_Attachment::fromPath($pdfFile)->setContentType('application/pdf')->setFilename('Полис ОСАГО.pdf'));
            }
            $this->get('mailer')->send($message);
        }
    }

    public function liqpayResponseAction(Request $request)
    {
        if ($this->payLiqpay($request))
            return new Response();
        else {
            $resp = new Response();
            $resp->setStatusCode(500);
            return $resp;
        }
    }

    public function privat24ResponseAction(Request $request)
    {
        $merchantPassword = $this->container->getParameter('privat24.password');
        if ($this->payPrivat24($request, $merchantPassword))
            return new Response();
        else {
            $resp = new Response();
            $resp->setStatusCode(500);
            return $resp;
        }

    }

    public function payRedirectAction(Request $request)
    {
        $error = true;
        $session = $request->getSession();
        try {
            $order = $this->getDoctrine()->getRepository('InsuranceContentBundle:InsuranceOrder')->findOneById($session->get('orderId'));
        } catch (Exception $e) {
            $paymentForm = $e->getMessage();
        }
        if ($order) {
            switch ($session->get('payType')) {
                case 'privat_card':
                case 'plastic':
                    $merchantId = $this->container->getParameter('liqpay.merchantId');
                    $merchantSign = $this->container->getParameter('liqpay.merchantSign');
                    $resultUrl = $this->generateUrl('payment_processing', array(), true);
                    $serverUrl = $this->generateUrl('liqpay', array(), true);
                    $price = sprintf('%.2f', $order->getTotalPrice());
                    $xml = <<<EOD
                                        <request>
                                            <version>1.2</version>
                                            <merchant_id>$merchantId</merchant_id>
                                            <result_url>$resultUrl?payment=liqpay</result_url>
                                            <server_url>$serverUrl</server_url>
                                            <order_id>{$order->getId()}</order_id>
                                            <amount>{$price}</amount>
                                            <default_phone></default_phone>
                                            <currency>UAH</currency>
                                            <description>Zakaz {$order->getId()}</description>
                                            <pay_way>card</pay_way>
                                            <goods_id>0</goods_id>
                                        </request>
EOD;
                    $xmlEnc = base64_encode($xml);

                    $sign=base64_encode(sha1($merchantSign.$xml.$merchantSign,1));
                    $paymentForm = <<<EOD
                                        <form action="https://www.liqpay.com/?do=clickNbuy" method="POST" id="payment-form"/>
                                                  <input type="hidden" name="operation_xml" value="$xmlEnc" />
                                                  <input type="hidden" name="signature" value="$sign" />
                                                  <button type="submit">Перейти</button>
                                        </form>
EOD;
                    $error = false;
                break;
                case 'privat24':
                    $resultUrl = $this->generateUrl('payment_processing', array(), true);
                    $serverUrl = $this->generateUrl('privat24', array(), true);
                    $description = 'Полис ОСАГО '. $order->getPolicy()->getSerie() .'/' . $order->getPolicy()->getValue() .
                        ($order->getPriceDgo() >0 ?', +ДГО':''). ($order->getPriceNs() >0 ?', +НС':'') . ', ' .
                        $order->getSurname() . ' ' . $order->getFirstname() . ' ' . $order->getMiddlename();
                    $paymentForm = <<<EOD
                                <form action="https://api.privatbank.ua:9083/p24api/ishop" method="post" id="payment-form" style="margin: 0px; padding: 0px;">
                                <input type="hidden" name="amt" value="{$order->getTotalPrice()}" />
                                <input type="hidden" name="ccy" value="UAH" />
                                <input type="hidden" name="merchant" value="76463" />
                                <input type="hidden" name="order" value="{$order->getId()}" />
                                <input type="hidden" name="details" value="$description" />
                                <input type="hidden" name="ext_details" value="" />
                                <input type="hidden" name="pay_way" value="privat24" />
                                <input type="hidden" name="return_url" value="$resultUrl" />
                                <input type="hidden" name="server_url" value="$serverUrl" />
                                <button type="submit">Перейти</button>
                                </form>
EOD;
                    $error = false;
                break;
                case 'webmoney':
                    $price = sprintf('%.2f', $order->getTotalPrice());
                    $description = 'Полис ОСАГО '. $order->getPolicy()->getSerie() .'№' . $order->getPolicy()->getValue() .
                        ($order->getPriceDgo() >0 ?', ДГО':''). ($order->getPriceNs() >0 ?', НС':'') . ', ' .
                        $order->getSurname() . ' ' . $order->getFirstname() . ' ' . $order->getMiddlename();
                    $webmoneyPurse = $this->container->getParameter('webmoney.purse');
                    $paymentForm = <<< EOD
                        <form method="POST" action="https://merchant.webmoney.ru/lmi/payment.asp" id="payment-form">
                        <input type="hidden" name="LMI_PAYMENT_AMOUNT" value="{$price}">
                        <input type="hidden" name="LMI_PAYMENT_DESC" value="{$description}">
                        <input type="hidden" name="LMI_PAYEE_PURSE" value="{$webmoneyPurse}">
                        <input type="hidden" name="id" value="{$order->getId()}">
                        <input type="text" name="email" size="15" value="{$order->getUser()->getEmail()}">
                        <input type="submit" value="Перейти">
                        </form>
EOD;
                    $error = false;
                break;
                default:
                    $paymentForm = 'Что-то пошло не так...';
                break;

            }
        } else {
            $paymentForm = 'Заказ не найден';
        }
        if ($error === false) {
            $session->remove('payType');
            $session->remove('orderId');
        }
        $feedbackForm = $this->createForm(new FeedbackType());
        return $this->render('InsuranceContentBundle:Default:payRedirect.html.twig',
            array(
                'error' => $error,
                'paymentForm' => $paymentForm,
                'feedback_form' => $feedbackForm->createView(),
                'callback_form' => $feedbackForm->createView(),
            ));
    }

    protected function payPrivat24(Request $request, $merchantPass)
    {
        $logger = $this->get('logger');
        $logger->info('Pay privat initialized');
        $logger->info('POST data:');
        $logger->info(var_export($request->request->all(), true));
        $logger->info('Generated signature:');
        $logger->info(sha1(md5($request->request->get('payment').$merchantPass)));
        if ($payment = $request->request->get('payment')) {
            $generatedSignature = sha1(md5($payment.$merchantPass));
            if ($generatedSignature == $request->request->get('signature')) {
                $logger->info('Signatures are equal');
                parse_str($payment, $payArray);
                $logger->info('Pay Array:');
                $logger->info(var_export($payArray, true));
                if ($payArray['state'] == 'test' || $payArray['state'] == 'ok' ) {
                    try {
                        $order = $this->getDoctrine()->getRepository('InsuranceContentBundle:InsuranceOrder')->findOneById($payArray['order']);
                        $order->setPayStatus(1);
                        $order->setPayDate(\DateTime::createFromFormat('dmyHis', $payArray['date']));
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($order);
                        $em->flush();
                    } catch (\Exception $e) {
                        $logger->info('Privat24 processing Doctrine error: ' . $e->getMessage());
                        return false;
                    }
                } else return false;
            } else return false;
        } else return false;
        return true;
    }

    protected function payLiqpay(Request $request)
    {
        $xmlEnc = $request->request->get('operation_xml');
        $xmlDecoded = base64_decode($xmlEnc);
        $receivedSign = $request->request->get('signature');
        //$merchantId = $this->container->getParameter('liqpay.merchantId');
        $merchantSign = $this->container->getParameter('liqpay.merchantSign');
        $logger = $this->get('logger');
        $logger->info('Liqpay  response: ');
        $logger->info('XML encoded: ');
        $logger->info($xmlEnc);
        $logger->info('XML decoded: ');
        $logger->info($xmlDecoded);
        $logger->info('Received signature:');
        $logger->info($receivedSign);
        $logger->info('Calculated signature:');
        $logger->info(base64_encode(sha1($merchantSign.$xmlDecoded.$merchantSign,1)));

        if (base64_encode(sha1($merchantSign.$xmlDecoded.$merchantSign,1)) == $receivedSign) {
            $xmlOb  = simplexml_load_string($xmlDecoded);
            $orderId = $xmlOb->order_id;
            if ($xmlOb->status == 'success'){
                $logger->info('Liqpay answer status: SUCCESS!');
                try {
                    $order = $this->getDoctrine()->getRepository('InsuranceContentBundle:InsuranceOrder')->findOneById($orderId);
                    $order->setPayStatus(1);
                    $order->setPayDate(new \DateTime('now'));
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($order);
                    $em->flush();
                    $logger->info('Liqpay payment succeeded!');
                } catch(Exception $e) {
                    $logger->info('Exception received in update order on Liqpay answer: ' .$e->getMessage());
                    return false;
                }
            } else {
                $logger->info('Liqpay payment status is fail!');
                return false;
            }
        } else {
            return false;
        }
        return true;
    }

    public function paymentProcessingAction(Request $request)
    {
        $session = $request->getSession();
        $logger = $this->get('logger');
        $logger->info('Payment success page visited.');
        $logger->info('Method: ' . var_export($request->getMethod(), true));
        $logger->info('Host: ' . var_export($request->getHost(), true));
        $logger->info('POST: ' . var_export($request->request->all(), true));
        $logger->info('GET: ' . var_export($request->query->all(), true));
        $merchantPassword = $this->container->getParameter('privat24.password');
        if ($request->getMethod() == 'POST' && $this->payPrivat24($request, $merchantPassword)) {
            $session->getFlashBag()->add('payStatus', 'success');
            return $this->redirect($this->generateUrl('payment_success'));
        } elseif ($request->query->get('payment') == 'liqpay' && $request->getMethod() == 'POST' && $this->payLiqpay($request)) {
            $session->getFlashBag()->add('payStatus', 'success');
            return $this->redirect($this->generateUrl('payment_success'));
        } elseif ($request->getMethod() == 'POST' && $request->query->get('payment') == 'webmoney' && 1 == $request->request->get('LMI_PREREQUEST')) {
            return $this->processWMPreRequest($request);
        } elseif ($request->getMethod() == 'POST' && $request->query->get('payment') == 'webmoney' && $this->payWebMoney($request)) {
            $session->getFlashBag()->add('payStatus', 'success');
            return $this->redirect($this->generateUrl('payment_success'));
        }
        else {
            return $this->redirect($this->generateUrl('homepage'));
        }

    }

    public function paymentSuccessAction(Request $request)
    {
        $session = $request->getSession();
        $feedbackForm = $this->createForm(new FeedbackType());
        $orderId = $session->get('orderId');
        $session->remove('orderId');
        $payStatus = $session->getFlashBag()->get('payStatus');
        if ($payStatus[0] == 'success') {
            $message = '<span class="success"></span><h3>Мы получили Вашу оплату!</h3><p>Спасибо за то, что воспользовались нашим сервисом! Наш менеджер свяжется с Вами в ближайшее время для уточнения деталей доставки.</p>';
        } elseif ($payStatus[0] == 'failure') {
            return $this->redirect($this->generateUrl('homepage'));
        } else
            return $this->redirect($this->generateUrl('homepage'));
        return $this->render('InsuranceContentBundle:Default:finish.html.twig', array(
            'feedback_form' => $feedbackForm->createView(),
            'callback_form' => $feedbackForm->createView(),
            'message' => $message,
            'orderId' => $orderId,
        ));
    }

    protected function processWMPreRequest(Request $request)
    {
        $logger = $this->get('logger');
        $orderId = $request->request->get('LMI_PAYMENT_NO');
        $payeePurse = $request->request->get('LMI_PAYEE_PURSE');
        $payAmount = $request->request->get('LMI_PAYMENT_AMOUNT');
        $webmoneyPurse = $this->container->getParameter('webmoney.purse');
        try {
            $order = $this->getDoctrine()->getRepository('InsuranceContentBundle:InsuranceOrder')->findOneById($orderId);
        } catch (\Exception $e) {
            $logger->error('WM prerequest error: ' . $e->getMessage());
            return new Response('Заказ не найден');
        }
        if ($order->getTotalPrice() != $payAmount) {
            $logger->error('WM prerequest wrong amount');
            return new Response('Суммы оплаты не совпадают');
        }
        if ($payeePurse != $webmoneyPurse) {
            $logger->error('WM prerequest wrong wallet (purse)');
            return new Response('Неверный кошелек');
        }
        return new Response('YES');
    }

    protected function payWebMoney(Request $request)
    {
        $logger = $this->get('logger');
        $orderId = $request->request->get('LMI_PAYMENT_NO');
        $payeePurse = $request->request->get('LMI_PAYEE_PURSE');
        $payAmount = $request->request->get('LMI_PAYMENT_AMOUNT');
        $mode = $request->request->get('LMI_MODE');
        $wmInvId = $request->request->get('LMI_SYS_INVS_NO');
        $wmOrderId = $request->request->get('LMI_SYS_TRANS_NO');
        $wmOrderDate = $request->request->get('LMI_SYS_TRANS_DATE');
        $secretKey = $this->container->getParameter('webmoney.secret');
        $payerPurse = $request->request->get('LMI_PAYER_PURSE');
        $payerWMId = $request->request->get('LMI_PAYER_WM');

        $receivedHash = $request->request->get('LMI_HASH');
        $calculatedHash = $payeePurse.$payAmount.$orderId.$mode.$wmInvId.$wmOrderId.$wmOrderDate.$secretKey.$payerPurse.$payerWMId;

        if ($receivedHash === $calculatedHash) {
            try {
                $order = $this->getDoctrine()->getRepository('InsuranceContentBundle:InsuranceOrder')->findOneById($orderId);
                $order->setPayStatus(1);
                $order->setPayDate(new \DateTime($wmOrderDate));
                $em = $this->getDoctrine()->getManager();
                $em->persist($order);
                $em->flush();
                $logger->info('WM payment succeeded!');
            } catch(Exception $e) {
                $logger->error('Exception received in update order on WM answer: ' .$e->getMessage());
                return false;
            }
        } else {
            $logger->error('WM payment hashes does not match');
            return false;
        }
        return false;
    }
}
