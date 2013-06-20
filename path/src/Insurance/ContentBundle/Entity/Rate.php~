<?php

namespace Insurance\ContentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Rate
 *
 * @ORM\Table(name="rate")
 * @ORM\Entity
 */
class Rate
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
     * @var string
     *
     * @ORM\Column(name="value", type="string", length=50, nullable=false)
     */
    private $value;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=20, nullable=false)
     */
    private $code;

    /**
     *
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="CompanyRate", mappedBy="rate")
     */
    private $companyRate;

    /**
     *
     * @var string
     * @ORM\Column(type="string", name="rate_type", columnDefinition="ENUM('base', 'ns', 'dgo')")
     */
    private $type;


    public function __construct()
    {
      $this->companyRate = new ArrayCollection();
    }

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
     * @return Rate
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
     * Set code
     *
     * @param string $code
     * @return Rate
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Add companyRate
     *
     * @param \Insurance\ContentBundle\Entity\CompanyRate $companyRate
     * @return Rate
     */
    public function addCompanyRate(\Insurance\ContentBundle\Entity\CompanyRate $companyRate)
    {
        $this->companyRate[] = $companyRate;

        return $this;
    }

    /**
     * Remove companyRate
     *
     * @param \Insurance\ContentBundle\Entity\CompanyRate $companyRate
     */
    public function removeCompanyRate(\Insurance\ContentBundle\Entity\CompanyRate $companyRate)
    {
        $this->companyRate->removeElement($companyRate);
    }

    /**
     * Get companyRate
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCompanyRate()
    {
        return $this->companyRate;
    }

    public function __toString()
    {
      return ($this->getValue()?$this->getValue():'');
    }

    /**
     * Set type
     *
     * @param string $type
     * @return Rate
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }
}
