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
            AND (rv.valueEqual = :value OR
            (rv.valueFrom <= :value AND rv.valueTo >= :value AND rv.valueEqual is null) OR
            (rv.valueFrom <= :value AND rv.valueTo IS NULL AND rv.valueEqual IS NULL) OR
            (rv.valueFrom IS NULL AND rv.valueTo >= :value AND rv.valueEqual is null))'
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

    /**
     * Calcalates common civil insurance for company
     */
    public function calculateCommon($fields)
    {
        $this->setCompany($fields['company'])
          ->setRateType('base');
        $k1Obj = $this->getRate($fields['region'], 'region');
        if ($k1Obj === null) $k1 = 1;
            else $k1 = $k1Obj->getValue();
        $k2Obj = $this->getRate($fields['displacement'], 'displacement');
        if ($k2Obj === null) $k2 = 1;
            else $k2 = $k2Obj->getValue();
        $k3Obj = $this->getRate($fields['experience'], 'experience');
        if ($k3Obj === null) $k3 = 1;
            else $k3 = $k3Obj->getValue();
        if ($fields['term'] < 1 && $fields['term'] > 0) $fields['term'] = str_replace ('.', ',', $fields['term']);
        $k4Obj = $this->getRate($fields['term'], 'term');
        if ($k4Obj === null) $k4 = 1;
            else $k4 = $k4Obj->getValue();
        //$k5 = $this->getRate($fields['year'], 'year')->getValue();
        $base = $this->sc->getParameter('base.rate');
        return $base * $k1 * $k2 * $k3 * $k4;
    }

    public function calculateDgo($fields)
    {
        $this->setCompany($fields['company'])
            ->setRateType('dgo');
        $k1Obj = $this->getRate($fields['sum'], 'dgo_summ');
        if ($k1Obj === null) $k1 = 1;
            else $k1 = $k1Obj->getValue();
        $k2Obj = $this->getRate($fields['displacement'], 'dgo_displacement');
        if ($k2Obj === null) $k2 = 1;
            else $k2 = $k2Obj->getValue();
        $k3Obj = $this->getRate($fields['experience'], 'dgo_experience');
        if ($k3Obj === null) $k3 = 1;
            else $k3 = $k3Obj->getValue();
        $k4Obj = $this->getRate($fields['term'], 'dgo_term');
        if ($k4Obj === null) $k4 = 1;
            else $k4 = $k4Obj->getValue();
        $k5Obj = $this->getRate($fields['taxi'], 'dgo_taxi'); //Here we must put 0 if car used as taxi
        if ($k5Obj === null) $k5 = 1;
            else $k5 = $k5Obj->getValue();
        //$ageRate = $this->sc->getParameter('dgo.rate');
        return $fields['sum'] * $k1 * $k2 * $k3 * $k4 * $k5;
    }

    public function calculateNs($fields)
    {
        $this->setCompany($fields['company'])
            ->setRateType('ns');
        $k1Obj = $this->getRate($fields['sum'], 'ns_driver');
        if ($k1Obj === null) $k1 = 1;
            else $k1 = $k1Obj->getValue();
        $k2Obj = $this->getRate($fields['sum'], 'ns_passenger');
        if ($k2Obj === null) $k2 = 1;
            else $k2 = $k2Obj->getValue();
        return $fields['sum'] * $k1 / 100 + ($fields['sum'] * $k2 / 100 * $fields['passenger_count']);
    }
}
?>
