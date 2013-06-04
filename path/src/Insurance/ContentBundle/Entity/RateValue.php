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
     * @var string
     *
     * @ORM\Column(name="value", type="string", length=100, nullable=false)
     */
    private $value;

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
     * Set value
     *
     * @param string $value
     * @return RateValue
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
      return $this->getValue();
    }
}
