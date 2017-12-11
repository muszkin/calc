<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Settings
 *
 * @ORM\Table(name="settings")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SettingsRepository")
 */
class Settings
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="shop", type="integer", unique=true)
     */
    private $shop;

    /**
     * @var string
     *
     * @ORM\Column(name="attribute", type="string")
     */
    private $attribute;

    /**
     * @var int
     *
     * @ORM\Column(name="attribute_id", type="integer")
     */
    private $attributeId;

    /**
     * @var string
     *
     * @ORM\Column(name="type_desc",type="string",nullable=true)
     */
    private $type_desc;

    /**
     * @var string
     *
     * @ORM\Column(name="dtext", type="string" ,nullable=true)
     */
    private $dtext;

    /**
     * @var int
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active;

    /**
     * @var int
     * @ORM\Column(name="new_attribute", type="integer",nullable=true)
     */
    private $new_attribute;

    /**
     * @var string
     * @ORM\Column(name="type2" ,type="string",nullable=true)
     */
    private $type2;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set shop
     *
     * @param integer $shop
     *
     * @return Settings
     */
    public function setShop($shop)
    {
        $this->shop = $shop;

        return $this;
    }

    /**
     * Get shop
     *
     * @return int
     */
    public function getShop()
    {
        return $this->shop;
    }

    /**
     * Set attributeId
     *
     * @param integer $attributeId
     *
     * @return Settings
     */
    public function setAttributeId($attributeId)
    {
        $this->attributeId = $attributeId;

        return $this;
    }

    /**
     * Get attributeId
     *
     * @return int
     */
    public function getAttributeId()
    {
        return $this->attributeId;
    }

    /**
     * @return string
     */
    public function getDtext()
    {
        return $this->dtext;
    }

    /**
     * @param string $dtext
     */
    public function setDtext($dtext)
    {
        $this->dtext = $dtext;
    }

    /**
     * @return int
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param int $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return string
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param string $attribute
     */
    public function setAttribute($attribute)
    {
        $this->attribute = $attribute;
    }

    /**
     * @return mixed
     */
    public function getTypeDesc()
    {
        return $this->type_desc;
    }

    /**
     * @param mixed $type_desc
     */
    public function setTypeDesc($type_desc)
    {
        $this->type_desc = $type_desc;
    }

    /**
     * @return mixed
     */
    public function getNewAttribute()
    {
        return $this->new_attribute;
    }

    /**
     * @param mixed $new_attribute
     */
    public function setNewAttribute($new_attribute)
    {
        $this->new_attribute = $new_attribute;
    }

    /**
     * Set type2
     *
     * @param string $type2
     *
     * @return Settings
     */
    public function setType2($type2)
    {
        $this->type2 = $type2;

        return $this;
    }

    /**
     * Get type2
     *
     * @return string
     */
    public function getType2()
    {
        return $this->type2;
    }
}
