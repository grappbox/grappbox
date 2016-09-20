<?php

namespace SQLBundle\Entity;

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
     * @var \SQLBundle\Entity\Project
     */
    private $projects;

    /**
     * @var \SQLBundle\Entity\EventType
     */
    private $eventtypes;

    /**
     * @var \SQLBundle\Entity\User
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
        $create = null;
        $edit = null;
        $begin = null;
        $end = null;
        if ($this->createdAt != null)
            $create = $this->createdAt->format('Y-m-d H:i:s');
        if ($this->editedAt != null)
            $edit = $this->editedAt->format('Y-m-d H:i:s');
        if ($this->beginDate != null)
            $begin = $this->beginDate->format('Y-m-d H:i:s');
        if ($this->endDate != null)
            $end = $this->endDate->format('Y-m-d H:i:s');
        if ($this->projects)
            $projectId = $this->projects->getId();
        return array(
            'id' => $this->id,
            'projectId' => $projectId,
            'creator' => array(
                'id' => $this->creator_user->getId(),
                'firstname' => $this->creator_user->getFirstName(),
                'lastname' => $this->creator_user->getLastName()
            ),
            'type' => array(
                'id' => $this->eventtypes->getId(),
                'name' => $this->eventtypes->getName()
            ),
            'title' => $this->title,
            'description' => $this->description,
            'beginDate' => $begin,
            'endDate' => $end,
            'createdAt' => $create,
            'editedAt' => $edit
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
     * Set projects
     *
     * @param \SQLBundle\Entity\Project $projects
     * @return Event
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
     * Set eventtypes
     *
     * @param \SQLBundle\Entity\EventType $eventtypes
     * @return Event
     */
    public function setEventtypes(\SQLBundle\Entity\EventType $eventtypes = null)
    {
        $this->eventtypes = $eventtypes;

        return $this;
    }

    /**
     * Get eventtypes
     *
     * @return \SQLBundle\Entity\EventType
     */
    public function getEventtypes()
    {
        return $this->eventtypes;
    }

    /**
     * Set creator_user
     *
     * @param \SQLBundle\Entity\User $creatorUser
     * @return Event
     */
    public function setCreatorUser(\SQLBundle\Entity\User $creatorUser = null)
    {
        $this->creator_user = $creatorUser;

        return $this;
    }

    /**
     * Get creator_user
     *
     * @return \SQLBundle\Entity\User
     */
    public function getCreatorUser()
    {
        return $this->creator_user;
    }

    /**
     * Add users
     *
     * @param \SQLBundle\Entity\User $users
     * @return Event
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
     * @var string
     */
    private $icon;


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
