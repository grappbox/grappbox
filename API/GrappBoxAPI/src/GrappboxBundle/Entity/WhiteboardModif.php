<?php

namespace GrappboxBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * WhiteboardModif
 */
class WhiteboardModif
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
     * @var integer
     */
    private $drawType;

    /**
     * @var string
     */
    private $path;

    /**
     * @var \DateTime
     */
    private $createdAt;


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
     * @return WhiteboardModif
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
     * Set drawType
     *
     * @param integer $drawType
     * @return WhiteboardModif
     */
    public function setDrawType($drawType)
    {
        $this->drawType = $drawType;

        return $this;
    }

    /**
     * Get drawType
     *
     * @return integer
     */
    public function getDrawType()
    {
        return $this->drawType;
    }

    /**
     * Set path
     *
     * @param string $path
     * @return WhiteboardModif
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return WhiteboardModif
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
}
