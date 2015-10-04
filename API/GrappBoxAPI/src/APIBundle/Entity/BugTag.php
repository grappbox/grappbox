<?php

namespace APIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BugTag
 */
class BugTag
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $bugId;

    /**
     * @var string
     */
    private $name;


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
     * Set bugId
     *
     * @param integer $bugId
     * @return BugTag
     */
    public function setBugId($bugId)
    {
        $this->bugId = $bugId;

        return $this;
    }

    /**
     * Get bugId
     *
     * @return integer 
     */
    public function getBugId()
    {
        return $this->bugId;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return BugTag
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
}
