<?php

namespace MongoBundle\Document;



/**
 * MongoBundle\Document\WhiteboardObject
 */
class WhiteboardObject
{
    /**
     * @var $id
     */
    protected $id;

    /**
     * @var string $whiteboardId
     */
    private $whiteboardId;

    /**
     * @var string $object
     */
    protected $object;

    /**
     * @var date $createdAt
     */
    protected $createdAt;

    /**
     * @var date $deletedAt
     */
    protected $deletedAt;

    /**
     * @var MongoBundle\Document\Whiteboard
     */
    protected $whiteboard;

    public function objectToArray()
    {
        return array(
            'id' => $this->id,
            'whiteboardId' => $this->whiteboardId,
            'object' => json_decode($this->object),
            'createdAt' => $this->createdAt,
            'deletedAt' => $this->deletedAt
        );
    }

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
     * @param string $whiteboardId
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
     * @return string $whiteboardId
     */
    public function getWhiteboardId()
    {
        return $this->whiteboardId;
    }

    /**
     * Set object
     *
     * @param string $object
     * @return self
     */
    public function setObject($object)
    {
        $this->object = $object;
        return $this;
    }

    /**
     * Get object
     *
     * @return string $object
     */
    public function getObject()
    {
        return $this->object;
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

    /**
     * Set deletedAt
     *
     * @param date $deletedAt
     * @return self
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;
        return $this;
    }

    /**
     * Get deletedAt
     *
     * @return date $deletedAt
     * @return self
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;

        return $this;
    }

    /**
     * Set whiteboard
     *
     * @param MongoBundle\Document\Whiteboard $whiteboard
     * @return self
     */
    public function setWhiteboard($whiteboard)
    {
        $this->whiteboard = $whiteboard;
        return $this;
    }

    /**
     * Get whiteboard
     *
     * @return MongoBundle\Document\Whiteboard $whiteboard
     */
    public function getWhiteboard()
    {
        return $this->whiteboard;
    }
}
