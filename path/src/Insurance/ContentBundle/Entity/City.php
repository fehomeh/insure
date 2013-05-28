<?php

namespace Insurance\ContentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
/**
 * City
 *
 * @ORM\Table(name="city")
 * @ORM\Entity
 */
class City
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
     * @ORM\Column(name="value", type="string", length=80, nullable=false)
     */
    private $value;

    /**
     * @var \Region
     *
     * @ORM\ManyToOne(targetEntity="Region", inversedBy="city")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="region_id", referencedColumnName="id")
     * })
     */
    private $region;

    /**
     *
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="InsuranceOrder", mappedBy="city")
     */
    private $insuranceCity;

    /**
     *
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="InsuranceOrder", mappedBy="registerCity")
     */
    private $insuranceRegisterCity;

    /**
     *
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="InsuranceOrder", mappedBy="deliveryCity")
     */
    private $insuranceDeliveryCity;

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
     * @return City
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
     * Set region
     *
     * @param \Insurance\ContentBundle\Entity\Region $region
     * @return City
     */
    public function setRegion(\Insurance\ContentBundle\Entity\Region $region = null)
    {
        $this->region = $region;

        return $this;
    }

    /**
     * Get region
     *
     * @return \Insurance\ContentBundle\Entity\Region
     */
    public function getRegion()
    {
        return $this->region;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->insuranceCity = new \Doctrine\Common\Collections\ArrayCollection();
        $this->insuranceRegisterCity = new \Doctrine\Common\Collections\ArrayCollection();
        $this->insuranceDeliveryCity = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add insuranceCity
     *
     * @param \Insurance\ContentBundle\Entity\InsuranceOrder $insuranceCity
     * @return City
     */
    public function addInsuranceCity(\Insurance\ContentBundle\Entity\InsuranceOrder $insuranceCity)
    {
        $this->insuranceCity[] = $insuranceCity;

        return $this;
    }

    /**
     * Remove insuranceCity
     *
     * @param \Insurance\ContentBundle\Entity\InsuranceOrder $insuranceCity
     */
    public function removeInsuranceCity(\Insurance\ContentBundle\Entity\InsuranceOrder $insuranceCity)
    {
        $this->insuranceCity->removeElement($insuranceCity);
    }

    /**
     * Get insuranceCity
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getInsuranceCity()
    {
        return $this->insuranceCity;
    }

    /**
     * Add insuranceRegisterCity
     *
     * @param \Insurance\ContentBundle\Entity\InsuranceOrder $insuranceRegisterCity
     * @return City
     */
    public function addInsuranceRegisterCity(\Insurance\ContentBundle\Entity\InsuranceOrder $insuranceRegisterCity)
    {
        $this->insuranceRegisterCity[] = $insuranceRegisterCity;

        return $this;
    }

    /**
     * Remove insuranceRegisterCity
     *
     * @param \Insurance\ContentBundle\Entity\InsuranceOrder $insuranceRegisterCity
     */
    public function removeInsuranceRegisterCity(\Insurance\ContentBundle\Entity\InsuranceOrder $insuranceRegisterCity)
    {
        $this->insuranceRegisterCity->removeElement($insuranceRegisterCity);
    }

    /**
     * Get insuranceRegisterCity
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getInsuranceRegisterCity()
    {
        return $this->insuranceRegisterCity;
    }

    /**
     * Add insuranceDeliveryCity
     *
     * @param \Insurance\ContentBundle\Entity\InsuranceOrder $insuranceDeliveryCity
     * @return City
     */
    public function addInsuranceDeliveryCity(\Insurance\ContentBundle\Entity\InsuranceOrder $insuranceDeliveryCity)
    {
        $this->insuranceDeliveryCity[] = $insuranceDeliveryCity;

        return $this;
    }

    /**
     * Remove insuranceDeliveryCity
     *
     * @param \Insurance\ContentBundle\Entity\InsuranceOrder $insuranceDeliveryCity
     */
    public function removeInsuranceDeliveryCity(\Insurance\ContentBundle\Entity\InsuranceOrder $insuranceDeliveryCity)
    {
        $this->insuranceDeliveryCity->removeElement($insuranceDeliveryCity);
    }

    /**
     * Get insuranceDeliveryCity
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getInsuranceDeliveryCity()
    {
        return $this->insuranceDeliveryCity;
    }
}
