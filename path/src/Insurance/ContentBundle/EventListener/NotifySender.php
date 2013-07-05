<?php
namespace Insurance\ContentBundle\EventListener;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Insurance\ContentBundle\Entity\Feedback;
use Insurance\ContentBundle\Entity\InsuranceOrder;

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
    if ($entity instanceof InsuranceOrder) { die('ola');
      if ($entity->getPayStatus() == 0 || $entity->getPayType() == 'cash') {
          $from = $this->sc->getParameter('email.send.from');
          $to = $entity->getUser()->getEmail();
          $message = \Swift_Message::newInstance()
              ->setSubject('Вы оставили заказ стархового полиса на сайте.')
              ->setFrom($from)
              ->setTo($to)
              ->setBody(
                    $this->sc->get('templating')->render(
                        'InsuranceContentBundle:Notifications:unpayedOrderNotification.html.twig',
                        array('order' => $entity)
                )
          )
          //->attach(\Swift_Attachment::fromPath('my-document.pdf'))
          ;
          $this->sc->get('mailer')->send($message);
      }
    }
  }

  public function preUpdate(PreUpdateEventArgs $args)
  {
      $entity = $args->getEntity();
      if ($entity instanceof InsuranceOrder) {
          if ($args->getOldValue('payStatus') === false && $args->getNewValue('payStatus') === true ) {
              $from = $this->sc->getParameter('email.send.from');
              $to = $entity->getUser()->getEmail();
              $message = \Swift_Message::newInstance()
                  ->setSubject('Ваш полис успешно оплачен. В скоре Вы получите оригинал.')
                  ->setFrom($from)
                  ->setTo($to)
                  ->setBody(
                      $this->sc->get('templating')->render(
                          'InsuranceContentBundle:Notifications:policySendNotification.html.twig',
                          array('order' => $entity)
                      )
          )
          //->attach(\Swift_Attachment::fromPath('my-document.pdf'))
          ;
          $this->sc->get('mailer')->send($message);
          }
      }
  }
}

?>
