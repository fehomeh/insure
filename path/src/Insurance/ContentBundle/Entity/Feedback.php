<?php
namespace Insurance\ContentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Callback class used to store information about fulfilled user form to make callback to user
 * @ORM\Table(name="callback")
 * @ORM\Entity
 */
class Feedback
{
  const CALLBACK = 'C';
  const FEEDBACK = 'F';
  /**
   *
   * @var integer
   *
   * @ORM\Column(name="id", type="integer", nullable=false)
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="IDENTITY")
   */
  private $id;

  /**
   *
   * @var string
   * @ORM\Column(name="name", type="string", nullable=false, length=20 )
   */
  private $name;

 
  /**
   *
   * @var string
   *
   * @ORM\Column(name="phone_number", length=15, type="string", options={"default"=null}, nullable=true)
   */

  private $phoneNumber;

  /**
   *
   * @var string
   *
   * @ORM\Column(name="email", type="string", length=50, options={ "default"=null }, nullable=true)
   */
  private $email;

  /**
   *
   * @var text
   * @ORM\Column(name="question", type="text", options={ "default"=null }, nullable=true)
   */
  private $question;

  /**
   *
   * @var enum
   *
   * @ORM\Column(name="connection_type", type="string", length=1, columnDefinition="ENUM('C','F')", nullable=false)
   */
  private $connectionType;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Feedback
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

  

    /**
     * Set phoneNumber
     *
     * @param string $phoneNumber
     * @return Feedback
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * Get phoneNumber
     *
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Feedback
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set question
     *
     * @param string $question
     * @return Feedback
     */
    public function setQuestion($question)
    {
        $this->question = $question;

        return $this;
    }

    /**
     * Get question
     *
     * @return string
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Set connectionType
     *
     * @param string $connectionType
     * @return Feedback
     */
    public function setConnectionType($connectionType)
    {
        $this->connectionType = $connectionType;

        return $this;
    }

    /**
     * Get connectionType
     *
     * @return string
     */
    public function getConnectionType()
    {
        return $this->connectionType;
    }
}
