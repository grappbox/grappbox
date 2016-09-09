<?php

namespace MongoBundle\Document;



/**
 * MongoBundle\Document\CloudTransfer
 */
class CloudTransfer
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
     * @var string $path
     */
    protected $path;

    /**
     * @var date $creationDate
     */
    protected $creationDate;

    /**
     * @var date $deletionDate
     */
    protected $deletionDate;

    /**
     * @var string $password
     */
    protected $password;

    /**
     * @var int $creator_id
     */
    protected $creator_id;


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
     * Set path
     *
     * @param string $path
     * @return self
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * Get path
     *
     * @return string $path
     */
    public function getPath()
    {
        return $this->path;
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
     * Set deletionDate
     *
     * @param date $deletionDate
     * @return self
     */
    public function setDeletionDate($deletionDate)
    {
        $this->deletionDate = $deletionDate;
        return $this;
    }

    /**
     * Get deletionDate
     *
     * @return date $deletionDate
     */
    public function getDeletionDate()
    {
        return $this->deletionDate;
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
     * Set creatorId
     *
     * @param int $creatorId
     * @return self
     */
    public function setCreatorId($creatorId)
    {
        $this->creator_id = $creatorId;
        return $this;
    }

    /**
     * Get creatorId
     *
     * @return int $creatorId
     */
    public function getCreatorId()
    {
        return $this->creator_id;
    }
}
