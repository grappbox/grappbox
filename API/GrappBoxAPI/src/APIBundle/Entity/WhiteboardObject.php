<?php

namespace APIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * WhiteboardObject
 */
class WhiteboardObject implements \Serializable
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
     * @var \APIBundle\Entity\Whiteboard
     */
    private $whiteboard;

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
     * @param \APIBundle\Entity\Whiteboard $whiteboard
     * @return WhiteboardObject
     */
    public function setWhiteboard(\APIBundle\Entity\Whiteboard $whiteboard = null)
    {
        $this->whiteboard = $whiteboard;

        return $this;
    }

    /**
     * Get whiteboard
     *
     * @return \APIBundle\Entity\Whiteboard
     */
    public function getWhiteboards()
    {
        return $this->whiteboard;
    }

    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->whiteboardId,
            $this->object,
            $this->createdAt,
            $this->deletedAt
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->whiteboardId,
            $this->object,
            $this->createdAt,
            $this->deletedAt,
        ) = unserialize($serialized);
    }
}
