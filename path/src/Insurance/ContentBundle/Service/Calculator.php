<?php

/*
 * This serivce is used for calculate insurance sum
 */
namespace Insurance\ContentBundle\Service;

class Calculator {

    //Service container variable
    private $sc;
    //Doctrine entity manager
    private $manager;
    /**
     * Insurance company ID
     * @var int
     */
    private $insuranceCompany;

    /**
     * Rate type to get
     * @var string
     */
    private $rateType;

    /**
     *
     * @param type $sc
     */
    public function __construct($sc)
    {
        $this->sc = $sc;
        $this->manager = $this->sc->get('doctrine')->getManager();
    }
    /**
     * Set insurance company ID
     * @param int $company
     */
    public function setCompany($company)
    {
        $this->insuranceCompany = $company;
        return $this;
    }
    /**
     * Set type of rate
     * @param string $rateType
     */
    public function setRateType($rateType)
    {
        $this->rateType = $rateType;
        return $this;
    }
    /**
     * Get rate from database by setted params
     * @param type $value
     * @param type $code
     */
    public function getRate($value, $code)
    {
        $query = $this->manager->createQuery(
          'SELECT cr FROM InsuranceContentBundle:CompanyRate cr
          JOIN cr.rate r
          JOIN cr.rateValue rv
          WHERE cr.company = :companyRate
          AND r.code = :rateCode
          AND r.type = :rateType
          AND (rv.valueEqual = :value OR (rv.valueFrom <= :value AND rv.valueTo >= :value AND rv.valueEqual is null))'
          )
          ->setParameter('companyRate', $this->insuranceCompany)
          ->setParameter('rateCode', $code)
          ->setParameter('rateType', $this->rateType)
          ->setParameter('value', $value)
        ;
        //var_dump($query->getSQL());
        try{
           $result = $query->getSingleResult();
        } catch(\Doctrine\ORM\NoResultException $e) {
            $result = null;
        }
        return $result;
    }
}
?>
