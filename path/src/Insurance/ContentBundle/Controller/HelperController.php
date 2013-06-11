<?php

namespace Insurance\ContentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Insurance\ContentBundle\Entity\InsuranceCompany;
use Insurance\ContentBundle\Entity\Policy;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Insurance\ContentBundle\Form\PolicyGenerateType;

class HelperController extends Controller
{
  public function generatePolicyAction (Request $request = null)
  {
    $form = $this->createForm(new PolicyGenerateType());
    return $this->render('InsuranceContentBundle:Helper:generate_policy.html.twig', array('form' => $form->createView()));
  }
}

?>
