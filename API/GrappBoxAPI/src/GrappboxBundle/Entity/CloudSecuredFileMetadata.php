<?php

namespace APIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CloudSecuredFileMetadata
 */
class CloudSecuredFileMetadata
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $filename;

    /**
     * @var string
     */
    private $password;

    /**
     * @var \DateTime
     */
    private $creationDate;

    /**
     * @var string
     */
    private $cloudPath;


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
     * @return CloudSecuredFileMetadata
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

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
     * Set password
     *
     * @param string $password
     * @return CloudSecuredFileMetadata
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

    /**
     * Set creationDate
     *
     * @param \DateTime $creationDate
     * @return CloudSecuredFileMetadata
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
     * Set cloudPath
     *
     * @param string $cloudPath
     * @return CloudSecuredFileMetadata
     */
    public function setCloudPath($cloudPath)
    {
        $this->cloudPath = $cloudPath;

        return $this;
    }

    /**
     * Get cloudPath
     *
     * @return string 
     */
    public function getCloudPath()
    {
        return $this->cloudPath;
    }
}
