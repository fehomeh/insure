<?php
namespace Insurance\ContentBundle\Controller

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Insurance\ContentBundle\Entity\Feedback;

class PDFController extends Controller {
    
    public function notifyFeedbackAction($feedbackId)
    {
        try{
            $feedback = $this->getDoctrine()->getRepository('InsuranceContentBundle:Feedback')->findOneById($feedbackId);
        } catch (Excepltion $e){
            return $this->render($e->getMessage(), 404);
        } 
    } 
}

?>
