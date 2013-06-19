<?php
namespace Insurance\ContentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Insurance\ContentBundle\Entity\Feedback;
use Symfony\Component\HttpFoundation\Response;

class PDFController extends Controller {

    public function notifyFeedbackAction($feedbackId)
    {
        try{
            $feedback = $this->getDoctrine()->getRepository('InsuranceContentBundle:Feedback')->findOneById($feedbackId);
        } catch (Excepltion $e){
            return $this->render($e->getMessage(), 404);
        }
    }

    public function notifyUserPolicyAction($orderId)
    {
      try {
        $order = $this->getDoctrine()->getRepository('InsuranceContentBundle:InsuranceOrder')->findOneById($orderId);
        if (!is_null($order))
        return $this->render('InsuranceContentBundle:PDF:userPolicy.html.twig', array('order' => $order));
        else return new Response('<html><head></head><body>Not found!</body></html>', 404);
      } catch (Exception $e){
        return $this->render(new Response ($e->getMessage(), 404));
      }
    }
}

?>
