<?php

namespace Insurance\ContentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Insurance\ContentBundle\Entity\Region;

class DefaultController extends Controller
{
    public function indexAction($name = 'Stranger')
    {
      $region = new Region();
      //$em = $this->getDoctrine()->getManager();
      //$region->setValue('Киевская');
      //$em->persist($region);
      //$em->flush();
      $productRep = $this->getDoctrine()->getRepository('InsuranceContentBundle:Region');
      $reg = $productRep->find('1');
        return $this->render('InsuranceContentBundle:Default:index.html.twig', array('name' => $name, 'id' => 1));
    }
}
