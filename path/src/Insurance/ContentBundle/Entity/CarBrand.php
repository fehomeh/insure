<?php

namespace Insurance\ContentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CarBrand
 *
 * @ORM\Table(name="car_brand")
 * @ORM\Entity
 */
class CarBrand
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
     *
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="CarModel", mappedBy="brand")
     */
    private $model;

    public function __construct()
    {
        $this->model = new ArrayCollection();
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
     * @return CarBrand
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
     * Add model
     *
     * @param \Insurance\ContentBundle\Entity\CarModel $model
     * @return CarBrand
     */
    public function addModel(\Insurance\ContentBundle\Entity\CarModel $model)
    {
        $this->model[] = $model;

        return $this;
    }

    /**
     * Remove model
     *
     * @param \Insurance\ContentBundle\Entity\CarModel $model
     */
    public function removeModel(\Insurance\ContentBundle\Entity\CarModel $model)
    {
        $this->model->removeElement($model);
    }

    /**
     * Get model
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getModel()
    {
        return $this->model;
    }
}
