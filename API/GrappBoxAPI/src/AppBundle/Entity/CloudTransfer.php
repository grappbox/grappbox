<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CloudTransfer
 */
class CloudTransfer
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
     private $creator_id;

    /**
     * @var string
     */
    private $filename;

    /**
     * @var \DateTime
     */
    private $creationDate;

    /**
     * @var string
     */
    private $password;


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
     * Set filename
     *
     * @param string $filename
     * @return CloudTransfer
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Get transfer creator id
     *
     * @return integer
     */
     public function getCreatorId()
     {
       return $this->creator_id;
     }

     /**
      * Set transfer creator id
      *
      * @param integer $creator_id
      * @return CloudTransfer
      */
      public function setCreatorId($creatorId)
      {
        $this->creator_id = $creatorId;
        return $this;
      }

    /**
     * Get filename
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set creationDate
     *
     * @param \DateTime $creationDate
     * @return CloudTransfer
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * Get creationDate
     *
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return CloudTransfer
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }
}
