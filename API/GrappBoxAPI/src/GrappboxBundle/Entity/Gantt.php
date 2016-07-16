<?php

namespace GrappboxBundle\Entity;

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
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var \GrappboxBundle\Entity\Project
     */
    private $projects;

    /**
     * @var \GrappboxBundle\Entity\User
     */
    private $creator_user;

    /**
     * @var \GrappboxBundle\Entity\User
     */
    private $updator_user;

    public function objectToArray()
    {
        return array(
            'id' => $this->id,
            'projectId' => $this->projects,
            'creatorId' => $this->creator_user,
            'updatorId' => $this->updator_user,
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

    /**
     * Set projects
     *
     * @param \GrappboxBundle\Entity\Project $projects
     * @return Gantt
     */
    public function setProjects(\GrappboxBundle\Entity\Project $projects = null)
    {
        $this->projects = $projects;

        return $this;
    }

    /**
     * Get projects
     *
     * @return \GrappboxBundle\Entity\Project
     */
    public function getProjects()
    {
        return $this->projects;
    }

    /**
     * Set creator_user
     *
     * @param \GrappboxBundle\Entity\User $creatorUser
     * @return Gantt
     */
    public function setCreatorUser(\GrappboxBundle\Entity\User $creatorUser = null)
    {
        $this->creator_user = $creatorUser;

        return $this;
    }

    /**
     * Get creator_user
     *
     * @return \GrappboxBundle\Entity\User
     */
    public function getCreatorUser()
    {
        return $this->creator_user;
    }

    /**
     * Set updator_user
     *
     * @param \GrappboxBundle\Entity\User $updatorUser
     * @return Gantt
     */
    public function setUpdatorUser(\GrappboxBundle\Entity\User $updatorUser = null)
    {
        $this->updator_user = $updatorUser;

        return $this;
    }

    /**
     * Get updator_user
     *
     * @return \GrappboxBundle\Entity\User
     */
    public function getUpdatorUser()
    {
        return $this->updator_user;
    }
}
