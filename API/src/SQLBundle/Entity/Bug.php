<?php

namespace SQLBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Bug
 */
class Bug
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $parentId;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;

    /**
     * @var integer
     */
    private $stateId;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var \DateTime
     */
    private $editedAt;

    /**
     * @var \DateTime
     */
    private $deletedAt;

    /**
     * @var \Boolean
     */
    private $clientOrigin;

    /**
     * @var \SQLBundle\Entity\Project
     */
    private $projects;

    /**
     * @var \SQLBundle\Entity\User
     */
    private $creator;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $users;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $bugtracker_tags;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
        $this->tags = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get object content into array
     *
     * @return array
     */
    public function objectToArray()
    {
      return array(
        "id" => $this->id,
        "creator" => array("id" => $this->creator->getID(), "fullname" => $this->creator->getFirstname()." ".$this->creator->getLastName()),
        "projectId" => $this->projects->getId(),
        "title" => $this->title,
        "description" => $this->description,
        "parentId" => $this->parentId,
        "createdAt" => $this->createdAt,
        "editedAt" => $this->editedAt,
        "deletedAt" => $this->deletedAt,
        "clientOrigin" => $this->clientOrigin
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
     * Set parentId
     *
     * @param integer $parentId
     * @return Bug
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;

        return $this;
    }

    /**
     * Get parentId
     *
     * @return integer
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Bug
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Bug
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set stateId
     *
     * @param integer $stateId
     * @return Bug
     */
    public function setStateId($stateId)
    {
        $this->stateId = $stateId;

        return $this;
    }

    /**
     * Get stateId
     *
     * @return integer
     */
    public function getStateId()
    {
        return $this->stateId;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Bug
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
     * Set editedAt
     *
     * @param \DateTime $editedAt
     * @return Bug
     */
    public function setEditedAt($editedAt)
    {
        $this->editedAt = $editedAt;

        return $this;
    }

    /**
     * Get editedAt
     *
     * @return \DateTime
     */
    public function getEditedAt()
    {
        return $this->editedAt;
    }

    /**
     * Set deletedAt
     *
     * @param \DateTime $deletedAt
     * @return Bug
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
     * Set clientOrigin
     *
     * @param integer $clientOrigin
     * @return Bug
     */
    public function setClientOrigin($clientOrigin)
    {
        $this->clientOrigin = $clientOrigin;

        return $this;
    }

    /**
     * Get clientOrigin
     *
     * @return integer
     */
    public function getClientOrigin()
    {
        return $this->clientOrigin;
    }

    /**
     * Set projects
     *
     * @param \SQLBundle\Entity\Project $projects
     * @return Bug
     */
    public function setProjects(\SQLBundle\Entity\Project $projects = null)
    {
        $this->projects = $projects;

        return $this;
    }

    /**
     * Get projects
     *
     * @return \SQLBundle\Entity\Project
     */
    public function getProjects()
    {
        return $this->projects;
    }

    /**
     * Set creator
     *
     * @param \SQLBundle\Entity\User $creator
     * @return Bug
     */
    public function setCreator(\SQLBundle\Entity\User $creator = null)
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * Get creator
     *
     * @return \SQLBundle\Entity\User
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * Add users
     *
     * @param \SQLBundle\Entity\User $users
     * @return Bug
     */
    public function addUser(\SQLBundle\Entity\User $users)
    {
        $this->users[] = $users;

        return $this;
    }

    /**
     * Remove users
     *
     * @param \SQLBundle\Entity\User $users
     */
    public function removeUser(\SQLBundle\Entity\User $users)
    {
        $this->users->removeElement($users);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Add tags
     *
     * @param \SQLBundle\Entity\BugtrackerTag $tags
     * @return Bug
     */
    public function addTag(\SQLBundle\Entity\BugtrackerTag $tags)
    {
        $this->bugtracker_tags[] = $tags;

        return $this;
    }

    /**
     * Remove tags
     *
     * @param \SQLBundle\Entity\BugtrackerTag $tags
     */
    public function removeTag(\SQLBundle\Entity\BugtrackerTag $tags)
    {
        $this->bugtracker_tags->removeElement($tags);
    }

    /**
     * Get tags
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTags()
    {
        return $this->bugtracker_tags;
    }
}
