<?php

namespace MongoBundle\Document;

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
     * @var text $path
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
     * @var text $password
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
     * @param text $path
     * @return self
     */
    public function setPath(\text $path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * Get path
     *
     * @return text $path
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
