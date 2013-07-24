<?php

namespace Insurance\ContentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Insurance\ContentBundle\Entity\Region;
use Insurance\ContentBundle\Entity\City;
use Insurance\ContentBundle\Form\FeedbackType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Insurance\ContentBundle\Entity\Feedback;


class DefaultController extends Controller
{

    public function indexAction($name = 'Stranger')
    {
      $regions = $this->getDoctrine()->getRepository('InsuranceContentBundle:Region')->findAll();
      $carBrands = $this->getDoctrine()->getRepository('InsuranceContentBundle:CarBrand')->findAll();
      $feedbackForm = $this->createForm(new FeedbackType());
      $session = $this->getRequest()->getSession();
      if ($carBrand = $session->get('carBrand')) {
          $carModels = $this->getDoctrine()->getRepository('InsuranceContentBundle:CarModel')->findByBrand($carBrand);
      } else $carModels = array();
      //$region->setValue('Киевская');
      //$em->persist($region);
      //$em->flush();
      //$productRep = $this->getDoctrine()->getRepository('InsuranceContentBundle:Region');
      //$reg = $productRep->find('1');
        return $this->render('InsuranceContentBundle:Default:index.html.twig', array(
          'regions' => $regions,
          'brands' => $carBrands,
          'carBrand' => $carBrand,
          'models' => $carModels,
          'carModel' => $session->get('carModel'),
          'feedback_form' => $feedbackForm->createView(),
          'callback_form' => $feedbackForm->createView(),
          ));
    }

    public function getCitiesAction()
    {
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
    }

    public function getCarModelsAction()
    {
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
    }

    public function goToStepOneAction()
    {
        $session = $this->getRequest()->getSession();
        $request = $this->getRequest();
        $securityContext = $this->container->get('security.context');
        if( $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') ){
    // authenticated REMEMBERED, FULLY will imply REMEMBERED (NON anonymous)
        }
        $session->set('carBrand', $request->request->get('carBrand'));
        $session->set('carModel', $request->request->get('carModel'));
        return $this->redirect($this->generateUrl('step1'));
    }

    public function stepOneAction()
    {
        $session = $this->getRequest()->getSession();
        if ($carBrand = $session->get('carBrand')) {
          $models = $this->getDoctrine()->getRepository('InsuranceContentBundle:CarModel')
              ->findByBrand($carBrand);
        } else $models = null;
        if ($region = $session->get('region')) {
            $cities = $this->getDoctrine()->getRepository('InsuranceContentBundle:City')->findByRegion($region);
        } else $cities = null;
        return $this->render('InsuranceContentBundle:Default:step_one.html.twig',array(
            'carBrand' => $session->get('carBrand'),
            'carModel' => $session->get('carModel'),
            'brands' => $this->getDoctrine()->getRepository('InsuranceContentBundle:CarBrand')->findAll(),
            'models' => $models,
            'regions' => $this->getDoctrine()->getRepository('InsuranceContentBundle:Region')->findAll(),
            'cities' => $cities,
            'region' => $region,
            'city' => $session->get('city'),
            'carAgeFrom' => $this->container->getParameter('car.age.from'),
            'carAgeTo' => (int)date('Y'),
            'displacement' => $this->container->getParameter('displacement'),
            'insureTerm' => $this->container->getParameter('insure.period'),
            'dgoSum' => $this->container->getParameter('dgo.sum'),
            'nsSum' => $this->container->getParameter('ns.sum'),
            'passengers' => $this->container->getParameter('passengers'),
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

        //if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('feedback_show', array('id' => $entity->getId())));
        //}

        return $this->render('InsuranceContentBundle:Default:index.html.twig', array(
            'regions' => null,
            'brands' => null,
            'entity' => $entity,
            'feedback_form'   => $form->createView(),
            'callback_form'   => $form->createView(),
        ));
    }

    /**
     * Process calculator (save data to session) and redirect to next step - filling personal data
     *
     */
    public function processCalculatorAction(Request $request)
    {
        $session = $request->getSession();
        $calculator = $this->get('insurance.service.calculator');
        $carBrand = $request->request->get('carBrand');
        $carModel = $request->request->get('carModel');
        $carAge = $request->request->get('carAge');
        $displacement = $request->request->get('hDisplacement');
        $registerRegion = $request->request->get('registerRegion');
        $registerCity = $request->request->get('registerCity');
        $insuranceTerm = $request->request->get('insuranceTerm');
        $dgoSum = $request->request->get('hDGOSum');
        $nsSum = $request->request->get('hNSSum');
        if ($carBrand > 0) $session->set('carBrand', $carBrand);
        if ($carModel > 0) $session->set('carModel', $carModel);
        if ($displacement > 0) $session->set('displacement', $displacement);
        if ($carModel > 0) $session->set('carAge', $carModel);
        if ($registerRegion > 0) $session->set('registerRegion', $registerRegion);
        if ($registerCity > 0) $session->set('registerCity', $registerCity);
        if ($insuranceTerm > 0) $session->set('insuranceTerm', $insuranceTerm);
        if ($request->request->get('cbDGO') == 'yes' && $dgoSum > 0) $session->set('dgoSum', $dgoSum);
        if ($request->request->get('cbNS') == 'yes' && $nsSum > 0) $session->set('nsSum', $nsSum);
        $calculator->setRateType('base')
            ->setCompany(2);
        $k1 = $calculator->getRate('Киев', 'region');
        $k2 = $calculator->getRate('1.7', 'displacement');
        var_dump($k1->getValue(), $k2->getValue());
        //$calculator->setRateType('base')
        //    ->setCompany(2);
        //$k1 = $calculator->getRate('Киев', 'region');
        //$k2 = $calculator->getRate('1.7', 'displacement');
        var_dump($calculator->calculateCommon(array(
          'region' => 'Киев',
          'displacement' => '1.7',
          'experience' => '2',
          'term' => '12',
          'year' => '2005',
          'company' => '2',
        )));
        return new Response();
    }
}
