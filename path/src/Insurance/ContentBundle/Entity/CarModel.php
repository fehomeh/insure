<?php

namespace Insurance\ContentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
/**
 * CarModel
 *
 * @ORM\Table(name="car_model")
 * @ORM\Entity
 */
class CarModel
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
     * @ORM\Column(name="value", type="string", length=30, nullable=false)
     */
    private $value;

    /**
     * @var \CarBrand
     *
     * @ORM\ManyToOne(targetEntity="CarBrand", inversedBy="model")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="brand_id", referencedColumnName="id")
     * })
     */
    private $brand;

    /**
     *
     * @var ArrayCollection()
     * @ORM\OneToMany(targetEntity="InsuranceOrder", mappedBy="carModel")
     */
    private $order;

    public function __construct()
    {
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
     * Set value
     *
     * @param string $value
     * @return CarModel
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
     * Set brand
     *
     * @param \Insurance\ContentBundle\Entity\CarBrand $brand
     * @return CarModel
     */
    public function setBrand(\Insurance\ContentBundle\Entity\CarBrand $brand = null)
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * Get brand
     *
     * @return \Insurance\ContentBundle\Entity\CarBrand
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * Add order
     *
     * @param \Insurance\ContentBundle\Entity\InsuranceOrder $order
     * @return CarModel
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
}
