<?php

namespace MongoBundle\Document;

class WhiteboardModif
{

    /**
     * @var $id
     */
    protected $id;

    /**
     * @var int $whiteboardId
     */
    protected $whiteboardId;

    /**
     * @var int $drawType
     */
    protected $drawType;

    /**
     * @var string $path
     */
    protected $path;

    /**
     * @var date $createdAt
     */
    protected $createdAt;


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
     * Set whiteboardId
     *
     * @param int $whiteboardId
     * @return self
     */
    public function setWhiteboardId($whiteboardId)
    {
        $this->whiteboardId = $whiteboardId;
        return $this;
    }

    /**
     * Get whiteboardId
     *
     * @return int $whiteboardId
     */
    public function getWhiteboardId()
    {
        return $this->whiteboardId;
    }

    /**
     * Set drawType
     *
     * @param int $drawType
     * @return self
     */
    public function setDrawType($drawType)
    {
        $this->drawType = $drawType;
        return $this;
    }

    /**
     * Get drawType
     *
     * @return int $drawType
     */
    public function getDrawType()
    {
        return $this->drawType;
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
     * Set createdAt
     *
     * @param date $createdAt
     * @return self
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * Get createdAt
     *
     * @return date $createdAt
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}
