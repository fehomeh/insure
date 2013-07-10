<?php

namespace Insurance\ContentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InsuranceOrder
 *
 * @ORM\Table(name="insurance_order")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class InsuranceOrder
{
    const PAYTYPE_CASH = 'cash';
    const PAYTYPE_CARD = 'card';
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active;

    /**
     *
     * @var string (W -waiting, P - paid, C - confirmed)
     * @ORM\Column(name="status", type="string", length=1, nullable=false)
     */
    private $status;

    /**
     * @var float
     * @ORM\Column(name="price", type="decimal", scale=2, nullable=false)
     */
    private $price;

    /**
     *
     * @var float
     * @ORM\Column(name="price_dgo", type="decimal", precision=8, scale=2)
     */
    private $priceDgo;

    /**
     *
     * @var float
     * @ORM\Column(name="price_ns", type="decimal", precision=8, scale=2)
     */
    private $priceNs;
    /**
     *
     * @var float
     *
     * @ORM\Column(name="sum_dgo", type="decimal", precision=8, scale=2, nullable=true)
     */
    private $sumDgo;

    /**
     * @var float
     *
     * @ORM\Column(name="sum_ns", type="decimal", precision=8, scale=2, nullable=true)
     */
    private $sumNs;

    /**
     *
     * @var integer
     *
     * @ORM\Column(name="passenger_count", type="integer", nullable=true)
     */
    private $passengerCount;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="active_from", type="date", nullable=false)
     */
    private $activeFrom;

    /**
     * @var string
     *
     * @ORM\Column(name="vin_code", type="string", length=20, nullable=false)
     */
    private $vinCode;

    /**
     * @var string
     *
     * @ORM\Column(name="car_number", type="string", length=20, nullable=false)
     */
    private $carNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="surname", type="string", length=20, nullable=false)
     */
    private $surname;

    /**
     * @var string
     *
     * @ORM\Column(name="firstname", type="string", length=30, nullable=false)
     */
    private $firstname;

    /**
     * @var string
     *
     * @ORM\Column(name="middlename", type="string", length=30, nullable=false)
     */
    private $middlename;

    /**
     * @var string
     *
     * @ORM\Column(name="document_type", type="string", length=1, nullable=false)
     */
    private $documentType;

    /**
     * @var string
     *
     * @ORM\Column(name="document_serie", type="string", length=2, nullable=true)
     */
    private $documentSerie;

    /**
     * @var string
     *
     * @ORM\Column(name="document_number", type="string", length=10, nullable=true)
     */
    private $documentNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="document_authority", type="string", length=250, nullable=false)
     */
    private $documentAuthority;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="document_date", type="date", nullable=false)
     */
    private $documentDate;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=20, nullable=false)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="register_address", type="string", length=250, nullable=false)
     */
    private $registerAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="register_building", type="string", length=5, nullable=false)
     */
    private $registerBuilding;

    /**
     * @var string
     *
     * @ORM\Column(name="delivery_address", type="string", length=250, nullable=false)
     */
    private $deliveryAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="delivery_building", type="string", length=5, nullable=false)
     */
    private $deliveryBuilding;

    /**
     * @var boolean
     *
     * @ORM\Column(name="pay_status", type="boolean", nullable=false)
     */
    private $payStatus;

    /**
     * @var string
     *
     * @ORM\Column(name="pay_type", type="string", length=20, nullable=false)
     */
    private $payType;

    /**
     * @var \CarModel
     *
     * @ORM\ManyToOne(targetEntity="CarModel", inversedBy="order")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="car_model_id", referencedColumnName="id")
     * })
     */
    private $carModel;

    /**
     * @var \Application\Sonata\UserBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="\Application\Sonata\UserBundle\Entity\User", inversedBy="insuranceOrder")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $user;

    /**
     * @var \InsuranceCompany
     *
     * @ORM\ManyToOne(targetEntity="InsuranceCompany", inversedBy="order")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="company_id", referencedColumnName="id")
     * })
     */
    private $company;

    /**
     * @var \City
     *
     * @ORM\ManyToOne(targetEntity="City", inversedBy="insuranceCity")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="city_id", referencedColumnName="id")
     * })
     */
    private $city;

    /**
     * @var \City
     *
     * @ORM\ManyToOne(targetEntity="City", inversedBy="insuranceRegisterCity")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="register_city_id", referencedColumnName="id")
     * })
     */
    private $registerCity;

    /**
     * @var \City
     *
     * @ORM\ManyToOne(targetEntity="City", inversedBy="insuranceDeliveryCity")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="delivery_city_id", referencedColumnName="id")
     * })
     */
    private $deliveryCity;

    /**
     * @var \Policy
     * @ORM\OneToOne(targetEntity="Policy", inversedBy="order")
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="policy_id", referencedColumnName="id")
     * })
     *
     */
    private $policy;

    /**
     *
     * @var string
     * @ORM\Column(nullable=true)
     *
     */
    private $pdfUrl;

    /**
     *
     * @var \DateTime
     * @ORM\Column(type="datetime", name="order_date")
     */
    private $orderDate;

    /**
     *
     * @var \DateTime
     * @ORM\Column(type="datetime", name="pay_date", nullable=true)
     */
    private $payDate;


    public function onPrePersistSetOrderDate()
    {
      $this->orderDate = new \DateTime();
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
     * Set active
     *
     * @param boolean $active
     * @return InsuranceOrder
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set activeFrom
     *
     * @param \DateTime $activeFrom
     * @return InsuranceOrder
     */
    public function setActiveFrom($activeFrom)
    {
        $this->activeFrom = $activeFrom;

        return $this;
    }

    /**
     * Get activeFrom
     *
     * @return \DateTime
     */
    public function getActiveFrom()
    {
        return $this->activeFrom;
    }

    /**
     * Set vinCode
     *
     * @param string $vinCode
     * @return InsuranceOrder
     */
    public function setVinCode($vinCode)
    {
        $this->vinCode = $vinCode;

        return $this;
    }

    /**
     * Get vinCode
     *
     * @return string
     */
    public function getVinCode()
    {
        return $this->vinCode;
    }

    /**
     * Set carNumber
     *
     * @param string $carNumber
     * @return InsuranceOrder
     */
    public function setCarNumber($carNumber)
    {
        $this->carNumber = $carNumber;

        return $this;
    }

    /**
     * Get carNumber
     *
     * @return string
     */
    public function getCarNumber()
    {
        return $this->carNumber;
    }

    /**
     * Set surname
     *
     * @param string $surname
     * @return InsuranceOrder
     */
    public function setSurname($surname)
    {
        $this->surname = $surname;

        return $this;
    }

    /**
     * Get surname
     *
     * @return string
     */
    public function getSurname()
    {
        return $this->surname;
    }

    /**
     * Set firstname
     *
     * @param string $firstname
     * @return InsuranceOrder
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get firstname
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set middlename
     *
     * @param string $middlename
     * @return InsuranceOrder
     */
    public function setMiddlename($middlename)
    {
        $this->middlename = $middlename;

        return $this;
    }

    /**
     * Get middlename
     *
     * @return string
     */
    public function getMiddlename()
    {
        return $this->middlename;
    }

    /**
     * Set documentType
     *
     * @param string $documentType
     * @return InsuranceOrder
     */
    public function setDocumentType($documentType)
    {
        $this->documentType = $documentType;

        return $this;
    }

    /**
     * Get documentType
     *
     * @return string
     */
    public function getDocumentType()
    {
        return $this->documentType;
    }

    /**
     * Set documentSerie
     *
     * @param string $documentSerie
     * @return InsuranceOrder
     */
    public function setDocumentSerie($documentSerie)
    {
        $this->documentSerie = $documentSerie;

        return $this;
    }

    /**
     * Get documentSerie
     *
     * @return string
     */
    public function getDocumentSerie()
    {
        return $this->documentSerie;
    }

    /**
     * Set documentNumber
     *
     * @param string $documentNumber
     * @return InsuranceOrder
     */
    public function setDocumentNumber($documentNumber)
    {
        $this->documentNumber = $documentNumber;

        return $this;
    }

    /**
     * Get documentNumber
     *
     * @return string
     */
    public function getDocumentNumber()
    {
        return $this->documentNumber;
    }

    /**
     * Set documentAuthority
     *
     * @param string $documentAuthority
     * @return InsuranceOrder
     */
    public function setDocumentAuthority($documentAuthority)
    {
        $this->documentAuthority = $documentAuthority;

        return $this;
    }

    /**
     * Get documentAuthority
     *
     * @return string
     */
    public function getDocumentAuthority()
    {
        return $this->documentAuthority;
    }

    /**
     * Set documentDate
     *
     * @param \DateTime $documentDate
     * @return InsuranceOrder
     */
    public function setDocumentDate($documentDate)
    {
        $this->documentDate = $documentDate;

        return $this;
    }

    /**
     * Get documentDate
     *
     * @return \DateTime
     */
    public function getDocumentDate()
    {
        return $this->documentDate;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return InsuranceOrder
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set registerAddress
     *
     * @param string $registerAddress
     * @return InsuranceOrder
     */
    public function setRegisterAddress($registerAddress)
    {
        $this->registerAddress = $registerAddress;

        return $this;
    }

    /**
     * Get registerAddress
     *
     * @return string
     */
    public function getRegisterAddress()
    {
        return $this->registerAddress;
    }

    /**
     * Set registerBuilding
     *
     * @param string $registerBuilding
     * @return InsuranceOrder
     */
    public function setRegisterBuilding($registerBuilding)
    {
        $this->registerBuilding = $registerBuilding;

        return $this;
    }

    /**
     * Get registerBuilding
     *
     * @return string
     */
    public function getRegisterBuilding()
    {
        return $this->registerBuilding;
    }

    /**
     * Set deliveryAddress
     *
     * @param string $deliveryAddress
     * @return InsuranceOrder
     */
    public function setDeliveryAddress($deliveryAddress)
    {
        $this->deliveryAddress = $deliveryAddress;

        return $this;
    }

    /**
     * Get deliveryAddress
     *
     * @return string
     */
    public function getDeliveryAddress()
    {
        return $this->deliveryAddress;
    }

    /**
     * Set payStatus
     *
     * @param boolean $payStatus
     * @return InsuranceOrder
     */
    public function setPayStatus($payStatus)
    {
        $this->payStatus = $payStatus;

        return $this;
    }

    /**
     * Get payStatus
     *
     * @return boolean
     */
    public function getPayStatus()
    {
        return $this->payStatus;
    }

    /**
     * Set payType
     *
     * @param string $payType
     * @return InsuranceOrder
     */
    public function setPayType($payType)
    {
        $this->payType = $payType;

        return $this;
    }

    /**
     * Get payType
     *
     * @return string
     */
    public function getPayType()
    {
        return $this->payType;
    }

    /**
     * Set carModel
     *
     * @param \Insurance\ContentBundle\Entity\CarModel $carModel
     * @return InsuranceOrder
     */
    public function setCarModel(\Insurance\ContentBundle\Entity\CarModel $carModel = null)
    {
        $this->carModel = $carModel;

        return $this;
    }

    /**
     * Get carModel
     *
     * @return \Insurance\ContentBundle\Entity\CarModel
     */
    public function getCarModel()
    {
        return $this->carModel;
    }

    /**
     * Set company
     *
     * @param \Insurance\ContentBundle\Entity\InsuranceCompany $company
     * @return InsuranceOrder
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
     * Set city
     *
     * @param \Insurance\ContentBundle\Entity\City $city
     * @return InsuranceOrder
     */
    public function setCity(\Insurance\ContentBundle\Entity\City $city = null)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return \Insurance\ContentBundle\Entity\City
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set registerCity
     *
     * @param \Insurance\ContentBundle\Entity\City $registerCity
     * @return InsuranceOrder
     */
    public function setRegisterCity(\Insurance\ContentBundle\Entity\City $registerCity = null)
    {
        $this->registerCity = $registerCity;

        return $this;
    }

    /**
     * Get registerCity
     *
     * @return \Insurance\ContentBundle\Entity\City
     */
    public function getRegisterCity()
    {
        return $this->registerCity;
    }

    /**
     * Set deliveryCity
     *
     * @param \Insurance\ContentBundle\Entity\City $deliveryCity
     * @return InsuranceOrder
     */
    public function setDeliveryCity(\Insurance\ContentBundle\Entity\City $deliveryCity = null)
    {
        $this->deliveryCity = $deliveryCity;

        return $this;
    }

    /**
     * Get deliveryCity
     *
     * @return \Insurance\ContentBundle\Entity\City
     */
    public function getDeliveryCity()
    {
        return $this->deliveryCity;
    }

    /**
     * Set deliveryBuilding
     *
     * @param string $deliveryBuilding
     * @return InsuranceOrder
     */
    public function setDeliveryBuilding($deliveryBuilding)
    {
        $this->deliveryBuilding = $deliveryBuilding;

        return $this;
    }

    /**
     * Get deliveryBuilding
     *
     * @return string
     */
    public function getDeliveryBuilding()
    {
        return $this->deliveryBuilding;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return InsuranceOrder
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set price
     *
     * @param float $price
     * @return InsuranceOrder
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set priceDgo
     *
     * @param float $priceDgo
     * @return InsuranceOrder
     */
    public function setPriceDgo($priceDgo)
    {
        $this->priceDgo = $priceDgo;

        return $this;
    }

    /**
     * Get priceDgo
     *
     * @return float
     */
    public function getPriceDgo()
    {
        return $this->priceDgo;
    }

    /**
     * Set priceNs
     *
     * @param float $priceNs
     * @return InsuranceOrder
     */
    public function setPriceNs($priceNs)
    {
        $this->priceNs = $priceNs;

        return $this;
    }

    /**
     * Get priceNs
     *
     * @return float
     */
    public function getPriceNs()
    {
        return $this->priceNs;
    }

    /**
     * Set policy
     *
     * @param \Insurance\ContentBundle\Entity\Policy $policy
     * @return InsuranceOrder
     */
    public function setPolicy(\Insurance\ContentBundle\Entity\Policy $policy = null)
    {
        $this->policy = $policy;

        return $this;
    }

    /**
     * Get policy
     *
     * @return \Insurance\ContentBundle\Entity\Policy
     */
    public function getPolicy()
    {
        return $this->policy;
    }

    /**
     * Set user
     *
     * @param \Application\Sonata\UserBundle\Entity\User $user
     * @return InsuranceOrder
     */
    public function setUser(\Application\Sonata\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Application\Sonata\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set pdfUrl
     *
     * @param string $pdfUrl
     * @return InsuranceOrder
     */
    public function setPdfUrl($pdfUrl)
    {
        $this->pdfUrl = $pdfUrl;

        return $this;
    }

    /**
     * Get pdfUrl
     *
     * @return string
     */
    public function getPdfUrl()
    {
        return $this->pdfUrl;
    }


    /**
     * Set orderDate
     *
     * @param \DateTime $orderDate
     * @return InsuranceOrder
     */
    public function setOrderDate($orderDate)
    {
        $this->orderDate = $orderDate;

        return $this;
    }

    /**
     * Get orderDate
     *
     * @return \DateTime
     */
    public function getOrderDate()
    {
        return $this->orderDate;
    }

    /**
     * Set payDate
     *
     * @param \DateTime $payDate
     * @return InsuranceOrder
     */
    public function setPayDate($payDate)
    {
        $this->payDate = $payDate;

        return $this;
    }

    /**
     * Get payDate
     *
     * @return \DateTime
     */
    public function getPayDate()
    {
        return $this->payDate;
    }

    /**
     * Set sumDgo
     *
     * @param float $sumDgo
     * @return InsuranceOrder
     */
    public function setSumDgo($sumDgo)
    {
        $this->sumDgo = $sumDgo;

        return $this;
    }

    /**
     * Get sumDgo
     *
     * @return float 
     */
    public function getSumDgo()
    {
        return $this->sumDgo;
    }

    /**
     * Set sumNs
     *
     * @param float $sumNs
     * @return InsuranceOrder
     */
    public function setSumNs($sumNs)
    {
        $this->sumNs = $sumNs;

        return $this;
    }

    /**
     * Get sumNs
     *
     * @return float 
     */
    public function getSumNs()
    {
        return $this->sumNs;
    }

    /**
     * Set passengerCount
     *
     * @param integer $passengerCount
     * @return InsuranceOrder
     */
    public function setPassengerCount($passengerCount)
    {
        $this->passengerCount = $passengerCount;

        return $this;
    }

    /**
     * Get passengerCount
     *
     * @return integer 
     */
    public function getPassengerCount()
    {
        return $this->passengerCount;
    }
}
