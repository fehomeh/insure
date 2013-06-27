<?php

namespace Insurance\ContentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * RateValue
 *
 * @ORM\Table(name="rate_value")
 * @ORM\Entity
 */
class RateValue
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
     * @ORM\Column(name="value_from", type="decimal", nullable=true, precision=8, scale=2)
     */
    private $valueFrom;

    /**
     *
     * @var float
     * @ORM\Column(name="value_to", type="decimal", precision=8, scale=2, nullable=true)
     */
    private $valueTo;

    /**
     *
     * @var float
     * @ORM\Column(name="value_equal", type="string", length=50, nullable=true)
     */
    private $valueEqual;


    /**
     *
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="CompanyRate", mappedBy="rateValue")
     */
    private $companyRate;

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
     * Add companyRate
     *
     * @param \Insurance\ContentBundle\Entity\CompanyRate $companyRate
     * @return RateValue
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
      return ('От ' . $this->getValueFrom() . ' до ' . $this->getValueTo() . ' | точно ' . $this->getValueEqual());
    }

    /**
     * Set valueFrom
     *
     * @param float $valueFrom
     * @return RateValue
     */
    public function setValueFrom($valueFrom)
    {
        $this->valueFrom = $valueFrom;

        return $this;
    }

    /**
     * Get valueFrom
     *
     * @return float
     */
    public function getValueFrom()
    {
        return $this->valueFrom;
    }

    /**
     * Set valueTo
     *
     * @param float $valueTo
     * @return RateValue
     */
    public function setValueTo($valueTo)
    {
        $this->valueTo = $valueTo;

        return $this;
    }

    /**
     * Get valueTo
     *
     * @return float
     */
    public function getValueTo()
    {
        return $this->valueTo;
    }

    /**
     * Set valueEqual
     *
     * @param string $valueEqual
     * @return RateValue
     */
    public function setValueEqual($valueEqual)
    {
        $this->valueEqual = $valueEqual;

        return $this;
    }

    /**
     * Get valueEqual
     *
     * @return string
     */
    public function getValueEqual()
    {
        return $this->valueEqual;
    }
}
