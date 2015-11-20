<?php

namespace APIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Gantt
 */
class Gantt
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $projectId;

    /**
     * @var integer
     */
    private $creatorId;

    /**
     * @var integer
     */
    private $updatorId;

    /**
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * @var \DateTime
     */
    private $createdAt;

    public function objectToArray()
    {
        return array(
            'id' => $this->id,
            'projectId' => $this->projectId,
            'creatorId' => $this->creatorId,
            'updatorId' => $this->updatorId,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt
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
     * Set projectId
     *
     * @param integer $projectId
     * @return Gantt
     */
    public function setProjectId($projectId)
    {
        $this->projectId = $projectId;

        return $this;
    }

    /**
     * Get projectId
     *
     * @return integer
     */
    public function getProjectId()
    {
        return $this->projectId;
    }

    /**
     * Set creatorId
     *
     * @param integer $creatorId
     * @return Gantt
     */
    public function setCreatorId($creatorId)
    {
        $this->creatorId = $creatorId;

        return $this;
    }

    /**
     * Get creatorId
     *
     * @return integer
     */
    public function getCreatorId()
    {
        return $this->creatorId;
    }

    /**
     * Set updatorId
     *
     * @param integer $updatorId
     * @return Gantt
     */
    public function setUpdatorId($updatorId)
    {
        $this->updatorId = $updatorId;

        return $this;
    }

    /**
     * Get updatorId
     *
     * @return integer
     */
    public function getUpdatorId()
    {
        return $this->updatorId;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return Gantt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Gantt
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
