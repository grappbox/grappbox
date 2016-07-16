<?php

namespace GrappboxBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Whiteboard
 */
class Whiteboard
{
    public function objectToArray()
    {
        return array(
            'id' => $this->id,
            'projectId' => $this->getProjects()->getId(),
            'user' => array("id" => $this->creator_user->getId(), "firstname" => $this->creator_user->getFirstname(), "lastname" => $this->creator_user->getLastname()),
            'name' => $this->name,
            'updator' => array("id" => $this->creator_user->getId(), "firstname" => $this->creator_user->getFirstname(), "lastname" => $this->creator_user->getLastname()),
            'updatedAt' => $this->updatedAt,
            'createdAt' => $this->createdAt,
            'deletedAt' => $this->deletedAt
        );
    }

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var \DateTime
     */
    private $deletedAt;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $objects;

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

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->objects = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set name
     *
     * @param string $name
     * @return Whiteboard
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return Whiteboard
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
     * @return Whiteboard
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
     * @return Whiteboard
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
     * Add objects
     *
     * @param \GrappboxBundle\Entity\WhiteboardObject $objects
     * @return Whiteboard
     */
    public function addObject(\GrappboxBundle\Entity\WhiteboardObject $objects)
    {
        $this->objects[] = $objects;

        return $this;
    }

    /**
     * Remove objects
     *
     * @param \GrappboxBundle\Entity\WhiteboardObject $objects
     */
    public function removeObject(\GrappboxBundle\Entity\WhiteboardObject $objects)
    {
        $this->objects->removeElement($objects);
    }

    /**
     * Get objects
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getObjects()
    {
        return $this->objects;
    }

    /**
     * Set projects
     *
     * @param \GrappboxBundle\Entity\Project $projects
     * @return Whiteboard
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
     * @return Whiteboard
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
     * @return Whiteboard
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
