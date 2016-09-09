<?php

namespace MongoBundle\Document;



/**
 * MongoBundle\Document\CloudSecuredFileMetadata
 */
class CloudSecuredFileMetadata
{
    /**
     * @var $id
     */
    protected $id;

    /**
     * @var string $filename
     */
    protected $filename;

    /**
     * @var string $password
     */
    protected $password;

    /**
     * @var date $creationDate
     */
    protected $creationDate;

    /**
     * @var string $cloudPath
     */
    protected $cloudPath;


    /**
     * Get id
     *
     * @return id $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set filename
     *
     * @param string $filename
     * @return self
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * Get filename
     *
     * @return string $filename
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return self
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Get password
     *
     * @return string $password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set creationDate
     *
     * @param date $creationDate
     * @return self
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
        return $this;
    }

    /**
     * Get creationDate
     *
     * @return date $creationDate
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Set cloudPath
     *
     * @param string $cloudPath
     * @return self
     */
    public function setCloudPath($cloudPath)
    {
        $this->cloudPath = $cloudPath;
        return $this;
    }

    /**
     * Get cloudPath
     *
     * @return string $cloudPath
     */
    public function getCloudPath()
    {
        return $this->cloudPath;
    }
}
