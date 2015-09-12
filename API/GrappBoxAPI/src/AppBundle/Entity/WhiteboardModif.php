<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * whiteboard_modif
 *
 * @ORM\Table(name="whiteboard_modifs")
 * @ORM\Entity
 */
class WhiteboardModif
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="whiteboard_id", type="integer")
     */
    private $whiteboardId;

    /**
     * @var integer
     *
     * @ORM\Column(name="draw_type", type="smallint")
     */
    private $drawType;

    /**
     * @var string
     *
     * @ORM\Column(name="path", type="string", length=255)
     */
    private $path;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
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
     * @return whiteboard_modif
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
     * @return whiteboard_modif
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
     * @return whiteboard_modif
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
     * @return whiteboard_modif
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
