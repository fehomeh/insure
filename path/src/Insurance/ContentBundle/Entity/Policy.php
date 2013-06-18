<?php

namespace Insurance\ContentBundle\Entity;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * Policy
 *
 * @ORM\Table(name="policy")
 * @ORM\Entity
 * @UniqueEntity({"serie", "value"})
 */
class Policy
{
    /**
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
     *
     * @ORM\Column(name="serie", type="string", length=20, nullable=false)
     */
    private $serie;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="string", length=20, nullable=false)
     * @Assert\NotBlank()
     */
    private $value;

    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean", nullable=false)
     * @Assert\NotBlank()
     */
    private $status;

    /**
     * @var \InsuranceCompany
     *
     * @ORM\ManyToOne(targetEntity="InsuranceCompany", inversedBy="policy")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="company_id", referencedColumnName="id")
     * })
     */
    private $company;

    /**
     * @var ArrayCollection
     * 
     * @ORM\OneToOne(targetEntity="InsuranceOrder", mappedBy="policy")
     */
    private $order;

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
     * Set value
     *
     * @param string $value
     * @return Policy
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set status
     *
     * @param boolean $status
     * @return Policy
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return boolean
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set company
     *
     * @param \Insurance\ContentBundle\Entity\InsuranceCompany $company
     * @return Policy
     */
    public function setCompany(\Insurance\ContentBundle\Entity\InsuranceCompany $company = null)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company
     *
     * @return \Insurance\ContentBundle\Entity\InsuranceCompany
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set serie
     *
     * @param string $serie
     * @return Policy
     */
    public function setSerie($serie)
    {
        $this->serie = $serie;

        return $this;
    }

    /**
     * Get serie
     *
     * @return string 
     */
    public function getSerie()
    {
        return $this->serie;
    }

    public function __toString() {
        return strtoupper($this->getSerie()) . ' ' . $this->getValue();
    }

    /**
     * Set order
     *
     * @param \Insurance\ContentBundle\Entity\InsuranceOrder $order
     * @return Policy
     */
    public function setOrder(\Insurance\ContentBundle\Entity\InsuranceOrder $order = null)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get order
     *
     * @return \Insurance\ContentBundle\Entity\InsuranceOrder 
     */
    public function getOrder()
    {
        return $this->order;
    }
}
