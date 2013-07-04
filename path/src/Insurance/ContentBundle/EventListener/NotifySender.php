<?php
namespace Insurance\ContentBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Insurance\ContentBundle\Entity\Feedback;

class NotifySender
{
  protected $sc;

  public function __construct($sc)
  {
    $this->sc = $sc;
  }

  public function postPersist(LifecycleEventArgs $args)
  {
    $entity = $args->getEntity();
    if ($entity instanceof Feedback) {
      //var_dump($entity);exit;
      $message = \Swift_Message::newInstance()
        ->setSubject('Hello Email')
        ->setFrom('send@example.com')
        ->setTo('recipient@example.com')
        ->setBody(
            $this->sc->get('templating')->render(
                'InsuranceContentBundle:Notifications:feedbackNotification.txt.twig'
                //array('name' => $name)
            )
        )
    ;
    $this->sc->get('mailer')->send($message);
    }
  }
}

?>
