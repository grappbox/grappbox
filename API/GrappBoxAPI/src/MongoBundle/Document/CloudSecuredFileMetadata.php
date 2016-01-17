<?php

namespace MongoBundle\Document;

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
     * @var text $password
     */
    protected $password;

    /**
     * @var date $creationDate
     */
    protected $creationDate;

    /**
     * @var text $cloudPath
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
     * @param text $password
     * @return self
     */
    public function setPassword(\text $password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Get password
     *
     * @return text $password
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
     * @param text $cloudPath
     * @return self
     */
    public function setCloudPath(\text $cloudPath)
    {
        $this->cloudPath = $cloudPath;
        return $this;
    }

    /**
     * Get cloudPath
     *
     * @return text $cloudPath
     */
    public function getCloudPath()
    {
        return $this->cloudPath;
    }
}
