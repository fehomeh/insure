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
      $session->set('carBrand', $request->request->get('carBrand'));
      $session->set('carModel', $request->request->get('carModel'));
      return $this->redirect($this->generateUrl('step1'));
    }

    public function stepOneAction()
    {
        $session = $this->getRequest()->getSession();
      var_dump($this->container->getParameter('displacement'));
      if ($carBrand = $session->get('carBrand')) {
        $models = $this->getDoctrine()->getRepository('InsuranceContentBundle:CarModel')
            ->findByBrand($carBrand);
      } else $models = null;
      
      return $this->render('InsuranceContentBundle:Default:step_one.html.twig',array(
        'carBrand' => $session->get('carBrand'),
        'carModel' => $session->get('carModel'),
        'brands' => $this->getDoctrine()->getRepository('InsuranceContentBundle:CarBrand')->findAll(),
        'models' => $models,
        'carAgeFrom' => $this->container->getParameter('car.age.from'),
        'carAgeTo' => (int)date('Y'),
        'displacement' => $this->container->getParameter('displacement'),
        'insureTerm' => $this->container->getParameter('insure.period'),
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
     * Process calculator data and redirect to next step - filling personal data
     * 
     */
    public function processCalculatorAction()
    {
        
    }
}
