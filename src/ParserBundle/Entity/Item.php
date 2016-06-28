<?php

namespace ParserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use ParserBundle\Model\ModelInterface;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @package ParserBundle\Entity
 * @ORM\Entity(repositoryClass="ParserBundle\Repository\ItemRepository")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="tblProductData", indexes={@ORM\Index(name="strProductCodeIndex", columns={"strProductCode"})})
 */
class Item implements ModelInterface
{
    /**
     * @var int
     * @ORM\Column(name="intProductDataId", type="integer", options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $intProductDataId;

    /**
     * @var string
     * @ORM\Column(name="strProductName", type="string", length=50)
     * @Assert\NotBlank(message="Product name is blank");
     */
    protected $strProductName;

    /**
     * @var string
     * @ORM\Column(name="strProductDesc", type="string", length=255)
     * @Assert\NotBlank(message="Product desc is blank");
     */
    protected $strProductDesc;

    /**
     * @var string
     * @ORM\Column(name="strProductCode", type="string", length=10, unique=true)
     * @Assert\NotBlank(message="Product code is blank")
     */
    protected $strProductCode;

    /**
     * @var DateTime
     * @ORM\Column(name="dtmAdded", type="datetime", nullable=true)
     */
    protected $dtmAdded;

    /**
     * @var DateTime
     * @ORM\Column(name="dtmDiscontinued", type="datetime", nullable=true)
     */
    protected $dtmDiscontinued;

    /**
     * @var DateTime
     * @ORM\Column(name="stmTimestamp", type="datetime")
     */
    protected $stmTimestamp;

    /**
     * @var int
     * @Assert\Type(type="numeric", message="Property stock should be of type numeric")
     * @Assert\NotBlank(message="Property stock is blank")
     * @ORM\Column(name="intStock", type="integer")
     */
    protected $intStock;

    /**
     * @var float
     * @Assert\Type(type="numeric", message="Property cost should be of type numeric")
     * @Assert\NotBlank(message="Property cost is blank")
     * @Assert\Range(
     *      min = 0,
     *      max = 1000,
     *      minMessage = "Item cost must be at least {{ limit }}",
     *      maxMessage = "Item cost cannot be taller than {{ limit }}"
     * )
     * @ORM\Column(name="fltCost", type="float", options={"unsigned"=true})
     */
    protected $fltCost;

    /**
     * Set strProductName
     *
     * @param string $strProductName
     *
     * @return Item
     */
    public function setStrProductName($strProductName)
    {
        $this->strProductName = $strProductName;

        return $this;
    }

    /**
     * Get strProductName
     *
     * @return string
     */
    public function getStrProductName()
    {
        return $this->strProductName;
    }

    /**
     * Set strProductDesc
     *
     * @param string $strProductDesc
     *
     * @return Item
     */
    public function setStrProductDesc($strProductDesc)
    {
        $this->strProductDesc = $strProductDesc;

        return $this;
    }

    /**
     * Set strProductCode
     *
     * @param string $strProductCode
     *
     * @return Item
     */
    public function setStrProductCode($strProductCode)
    {
        $this->strProductCode = $strProductCode;

        return $this;
    }

    /**
     * Get strProductCode
     *
     * @return string
     */
    public function getStrProductCode()
    {
        return $this->strProductCode;
    }

    /**
     * @ORM\PrePersist
     */
    public function setDtmAdded()
    {
        $this->dtmAdded = new \DateTime();

        return $this;
    }

    /**
     * set date only if discontinued equal yes
     *
     * @param $discontinued
     */
    public function setDtmDiscontinued($discontinued)
    {
        $this->dtmDiscontinued = ($discontinued === 'yes') ? new \DateTime() : null;
    }

    /**
     * @ORM\PrePersist
     */
    public function setStmTimestamp()
    {
        $this->stmTimestamp = new \DateTime();

        return $this;
    }

    /**
     * Set intStock
     *
     * @param integer $intStock
     *
     * @return Item
     */
    public function setIntStock($intStock)
    {
        $this->intStock = $intStock;

        return $this;
    }

    /**
     * Set fltCost
     *
     * @param float $fltCost
     *
     * @return Item
     */
    public function setFltCost($fltCost)
    {
        $this->fltCost = $fltCost;

        return $this;
    }
}
