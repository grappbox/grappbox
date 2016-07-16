<?php

namespace GrappboxBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Event
 */
class Event
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;
    
    /**
     * @var string
     */
    private $icon;

    /**
     * @var \DateTime
     */
    private $beginDate;

    /**
     * @var \DateTime
     */
    private $endDate;

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
     * @var \GrappboxBundle\Entity\Project
     */
    private $projects;

    /**
     * @var \GrappboxBundle\Entity\EventType
     */
    private $eventtypes;

    /**
     * @var \GrappboxBundle\Entity\User
     */
    private $creator_user;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $users;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function objectToArray()
    {
      $projectId = null;
      if ($this->projects)
        $projectId = $this->projects->getId();
        return array(
            'id' => $this->id,
            'projectId' => $projectId,
            'creator' => array(
                'id' => $this->creator_user->getId(),
                'fullname' => $this->creator_user->getFirstName()." ".$this->creator_user->getLastName()
            ),
            'type' => array(
                'id' => $this->eventtypes->getId(),
                'name' => $this->eventtypes->getName()
            ),
            'title' => $this->title,
            'description' => $this->description,
            'icon' => $this->icon,
            'beginDate' => $this->beginDate,
            'endDate' => $this->endDate,
            'createdAt' => $this->createdAt,
            'editedAt' => $this->editedAt,
            'deletedAt' => $this->deletedAt
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
     * Set title
     *
     * @param string $title
     * @return Event
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
     * @return Event
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
     * Set beginDate
     *
     * @param \DateTime $beginDate
     * @return Event
     */
    public function setBeginDate($beginDate)
    {
        $this->beginDate = $beginDate;

        return $this;
    }

    /**
     * Get beginDate
     *
     * @return \DateTime
     */
    public function getBeginDate()
    {
        return $this->beginDate;
    }

    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     * @return Event
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate
     *
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Event
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
     * @return Event
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
     * @return Event
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
     * Set projects
     *
     * @param \GrappboxBundle\Entity\Project $projects
     * @return Event
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
     * Set eventtypes
     *
     * @param \GrappboxBundle\Entity\EventType $eventtypes
     * @return Event
     */
    public function setEventtypes(\GrappboxBundle\Entity\EventType $eventtypes = null)
    {
        $this->eventtypes = $eventtypes;

        return $this;
    }

    /**
     * Get eventtypes
     *
     * @return \GrappboxBundle\Entity\EventType
     */
    public function getEventtypes()
    {
        return $this->eventtypes;
    }

    /**
     * Set creator_user
     *
     * @param \GrappboxBundle\Entity\User $creatorUser
     * @return Event
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
     * Add users
     *
     * @param \GrappboxBundle\Entity\User $users
     * @return Event
     */
    public function addUser(\GrappboxBundle\Entity\User $users)
    {
        $this->users[] = $users;

        return $this;
    }

    /**
     * Remove users
     *
     * @param \GrappboxBundle\Entity\User $users
     */
    public function removeUser(\GrappboxBundle\Entity\User $users)
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
     * Set icon
     *
     * @param string $icon
     * @return Event
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Get icon
     *
     * @return string 
     */
    public function getIcon()
    {
        return $this->icon;
    }
}
