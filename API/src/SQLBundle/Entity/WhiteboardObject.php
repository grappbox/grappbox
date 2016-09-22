<?php

namespace SQLBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * WhiteboardObject
 */
class WhiteboardObject
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $whiteboardId;

    /**
     * @var string
     */
    private $object;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var \DateTime
     */
    private $deletedAt;

    /**
     * @var \SQLBundle\Entity\Whiteboard
     */
    private $whiteboard;

    public function objectToArray()
    {
        return array(
            'id' => $this->id,
            'whiteboardId' => $this->whiteboardId,
            'object' => json_decode($this->object),
            'createdAt' => $this->createdAt ? $this->createdAt->format('Y-m-d H:i:s') : null,
            'deletedAt' => $this->deletedAt ? $this->deletedAt->format('Y-m-d H:i:s') : null
        );
    }

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
     * Set whiteboardId
     *
     * @param integer $whiteboardId
     * @return WhiteboardObject
     */
    public function setWhiteboardId($whiteboardId)
    {
        $this->whiteboardId = $whiteboardId;

        return $this;
    }

    /**
     * Get whiteboardId
     *
     * @return integer
     */
    public function getWhiteboardId()
    {
        return $this->whiteboardId;
    }

    /**
     * Set object
     *
     * @param string $object
     * @return WhiteboardObject
     */
    public function setObject($object)
    {
        $this->object = $object;

        return $this;
    }

    /**
     * Get object
     *
     * @return string
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return WhiteboardObject
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set deletedAt
     *
     * @param \DateTime $deletedAt
     * @return WhiteboardObject
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Get deletedAt
     *
     * @return \DateTime
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * Set whiteboard
     *
     * @param \SQLBundle\Entity\Whiteboard $whiteboard
     * @return WhiteboardObject
     */
    public function setWhiteboard(\SQLBundle\Entity\Whiteboard $whiteboard = null)
    {
        $this->whiteboard = $whiteboard;

        return $this;
    }

    /**
     * Get whiteboard
     *
     * @return \SQLBundle\Entity\Whiteboard
     */
    public function getWhiteboard()
    {
        return $this->whiteboard;
    }
}
