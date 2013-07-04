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
      //var_dump($this->sc->getParameter('admin_emails'));exit;
      $to = $this->sc->getParameter('admin.emails');
      $from = $this->sc->getParameter('email.send.from');
      $conType = $entity->getConnectionType();
      $message = \Swift_Message::newInstance()
        ->setSubject('Поступил новый ' . $conType == Feedback::CALLBACK ?
        'запрос на обратный звонок' : 'вопрос' )
        ->setFrom($conType == Feedback::CALLBACK ? $from : $entity->getEmail())
        ->setTo($to)
        ->setBody(
            $this->sc->get('templating')->render(
                'InsuranceContentBundle:Notifications:feedbackNotification.txt.twig',
                array('contact' => $entity)
            )
        )
        //->attach(\Swift_Attachment::fromPath('my-document.pdf'))
    ;
    $this->sc->get('mailer')->send($message);
    }
  }
}

?>
