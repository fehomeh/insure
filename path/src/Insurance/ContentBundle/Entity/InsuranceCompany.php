<?php

namespace Insurance\ContentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
/**
 * InsuranceCompany
 *
 * @ORM\Table(name="insurance_company")
 * @ORM\Entity
 */
class InsuranceCompany
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
     * @ORM\Column(name="name", type="string", length=100, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     *
     * @var string
     * @ORM\Column(name="requisites", type="text", nullable=true)
     */
    private $requisites;

    /**
     *
     * @var string
     * @ORM\Column(name="insurer_code", type="string", length=10, nullable=true)
     */
    private $insurerCode;

    /**
     *
     * @var string
     * @ORM\Column(name="franchise", type="string", length=100)
     */
    private $franchise;
    /**
     * @var string
     *
     * @ORM\Column(name="logo", type="string", length=200, nullable=true)
     */
    private $logo;

    /**
     * @var float
     *
     * @ORM\Column(name="default_rate", type="decimal", precision=8, scale=2, nullable=true)
     */
    private $defaultRate;

    /**
     *
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="CompanyRate", mappedBy="company", cascade={"persist", "remove"})
     */
    private $companyRate;

    /**
     *
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Policy", mappedBy="company")
     */
    private $policy;

    /**
     *
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="InsuranceOrder", mappedBy="company")
     */
    private $order;

    public function __construct()
    {
      $this->companyRate = new ArrayCollection();
      $this->policy = new ArrayCollection();
      $this->order = new ArrayCollection();
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
     * Set name
     *
     * @param string $name
     * @return InsuranceCompany
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
     * Set description
     *
     * @param string $description
     * @return InsuranceCompany
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set logo
     *
     * @param string $logo
     * @return InsuranceCompany
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;

        return $this;
    }

    /**
     * Get logo
     *
     * @return string
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * Set defaultRate
     *
     * @param float $defaultRate
     * @return InsuranceCompany
     */
    public function setDefaultRate($defaultRate)
    {
        $this->defaultRate = $defaultRate;

        return $this;
    }

    /**
     * Get defaultRate
     *
     * @return float
     */
    public function getDefaultRate()
    {
        return $this->defaultRate;
    }

    /**
     * Add companyRate
     *
     * @param \Insurance\ContentBundle\Entity\CompanyRate $companyRate
     * @return InsuranceCompany
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

    /**
     * Add policy
     *
     * @param \Insurance\ContentBundle\Entity\Policy $policy
     * @return InsuranceCompany
     */
    public function addPolicy(\Insurance\ContentBundle\Entity\Policy $policy)
    {
        $this->policy[] = $policy;

        return $this;
    }

    /**
     * Remove policy
     *
     * @param \Insurance\ContentBundle\Entity\Policy $policy
     */
    public function removePolicy(\Insurance\ContentBundle\Entity\Policy $policy)
    {
        $this->policy->removeElement($policy);
    }

    /**
     * Get policy
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPolicy()
    {
        return $this->policy;
    }

    /**
     * Add order
     *
     * @param \Insurance\ContentBundle\Entity\InsuranceOrder $order
     * @return InsuranceCompany
     */
    public function addOrder(\Insurance\ContentBundle\Entity\InsuranceOrder $order)
    {
        $this->order[] = $order;

        return $this;
    }

    /**
     * Remove order
     *
     * @param \Insurance\ContentBundle\Entity\InsuranceOrder $order
     */
    public function removeOrder(\Insurance\ContentBundle\Entity\InsuranceOrder $order)
    {
        $this->order->removeElement($order);
    }

    /**
     * Get order
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOrder()
    {
        return $this->order;
    }

    public function __toString()
    {
      return ($this->getName()?$this->getName():'');
    }

    /**
     * Set requisites
     *
     * @param string $requisites
     * @return InsuranceCompany
     */
    public function setRequisites($requisites)
    {
        $this->requisites = $requisites;

        return $this;
    }

    /**
     * Get requisites
     *
     * @return string
     */
    public function getRequisites()
    {
        return $this->requisites;
    }

    /**
     * Set insurerCode
     *
     * @param string $insurerCode
     * @return InsuranceCompany
     */
    public function setInsurerCode($insurerCode)
    {
        $this->insurerCode = $insurerCode;

        return $this;
    }

    /**
     * Get insurerCode
     *
     * @return string
     */
    public function getInsurerCode()
    {
        return $this->insurerCode;
    }

    /**
     * Set franchise
     *
     * @param string $franchise
     * @return InsuranceCompany
     */
    public function setFranchise($franchise)
    {
        $this->franchise = $franchise;

        return $this;
    }

    /**
     * Get franchise
     *
     * @return string
     */
    public function getFranchise()
    {
        return $this->franchise;
    }
}
