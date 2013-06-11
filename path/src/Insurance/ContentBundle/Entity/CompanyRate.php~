<?php

namespace Insurance\ContentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CompanyRate
 *
 * @ORM\Table(name="company_rate")
 * @ORM\Entity
 */
class CompanyRate
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
     * @var float
     *
     * @ORM\Column(name="value", type="decimal", nullable=false)
     */
    private $value;

    /**
     * @var \Rate
     *
     * @ORM\ManyToOne(targetEntity="Rate", inversedBy="companyRate")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="rate_id", referencedColumnName="id")
     * })
     */
    private $rate;

    /**
     * @var \InsuranceCompany
     *
     * @ORM\ManyToOne(targetEntity="InsuranceCompany", inversedBy="companyRate")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="company_id", referencedColumnName="id")
     * })
     */
    private $company;

    /**
     * @var \RateValue
     *
     * @ORM\ManyToOne(targetEntity="RateValue", inversedBy="companyRate")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="rate_value_id", referencedColumnName="id")
     * })
     */
    private $rateValue;



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
     * @param float $value
     * @return CompanyRate
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set rate
     *
     * @param \Insurance\ContentBundle\Entity\Rate $rate
     * @return CompanyRate
     */
    public function setRate(\Insurance\ContentBundle\Entity\Rate $rate = null)
    {
        $this->rate = $rate;

        return $this;
    }

    /**
     * Get rate
     *
     * @return \Insurance\ContentBundle\Entity\Rate
     */
    public function getRate()
    {
        return $this->rate;
    }

    /**
     * Set company
     *
     * @param \Insurance\ContentBundle\Entity\InsuranceCompany $company
     * @return CompanyRate
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
     * Set rateValue
     *
     * @param \Insurance\ContentBundle\Entity\RateValue $rateValue
     * @return CompanyRate
     */
    public function setRateValue(\Insurance\ContentBundle\Entity\RateValue $rateValue = null)
    {
        $this->rateValue = $rateValue;

        return $this;
    }

    /**
     * Get rateValue
     *
     * @return \Insurance\ContentBundle\Entity\RateValue
     */
    public function getRateValue()
    {
        return $this->rateValue;
    }

    public function __toString()
    {
      return ($this->getRate()?$this->getRate():'');
    }
}
