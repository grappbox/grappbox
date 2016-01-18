<?php

namespace MongoBundle\Document;



/**
 * MongoBundle\Document\Event
 */
class Event
{
    /**
     * @var $id
     */
    protected $id;

    /**
     * @var string $title
     */
    protected $title;

    /**
     * @var string $description
     */
    protected $description;

    /**
     * @var date $beginDate
     */
    protected $beginDate;

    /**
     * @var date $endDate
     */
    protected $endDate;

    /**
     * @var date $createdAt
     */
    protected $createdAt;

    /**
     * @var date $editedAt
     */
    protected $editedAt;

    /**
     * @var date $deletedAt
     */
    protected $deletedAt;

    /**
     * @var MongoBundle\Document\Project
     */
    protected $projects;

    /**
     * @var MongoBundle\Document\EventType
     */
    protected $eventtypes;

    /**
     * @var MongoBundle\Document\User
     */
    protected $creator_user;

    /**
     * @var MongoBundle\Document\User
     */
    protected $users = array();

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
            'creatorId' => $this->creator_user->getId(),
            'eventTypeId' => $this->eventtypes->getId(),
            'eventType' => $this->eventtypes->getName(),
            'title' => $this->title,
            'description' => $this->description,
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
     * @return id $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return self
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Get title
     *
     * @return string $title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get description
     *
     * @return string $description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set beginDate
     *
     * @param date $beginDate
     * @return self
     */
    public function setBeginDate($beginDate)
    {
        $this->beginDate = $beginDate;
        return $this;
    }

    /**
     * Get beginDate
     *
     * @return date $beginDate
     */
    public function getBeginDate()
    {
        return $this->beginDate;
    }

    /**
     * Set endDate
     *
     * @param date $endDate
     * @return self
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
        return $this;
    }

    /**
     * Get endDate
     *
     * @return date $endDate
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set createdAt
     *
     * @param date $createdAt
     * @return self
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * Get createdAt
     *
     * @return date $createdAt
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set editedAt
     *
     * @param date $editedAt
     * @return self
     */
    public function setEditedAt($editedAt)
    {
        $this->editedAt = $editedAt;
        return $this;
    }

    /**
     * Get editedAt
     *
     * @return date $editedAt
     */
    public function getEditedAt()
    {
        return $this->editedAt;
    }

    /**
     * Set deletedAt
     *
     * @param date $deletedAt
     * @return self
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;
        return $this;
    }

    /**
     * Get deletedAt
     *
     * @return date $deletedAt
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * Set projects
     *
     * @param MongoBundle\Document\Project $projects
     * @return self
     */
    public function setProjects(\MongoBundle\Document\Project $projects)
    {
        $this->projects = $projects;
        return $this;
    }

    /**
     * Get projects
     *
     * @return MongoBundle\Document\Project $projects
     */
    public function getProjects()
    {
        return $this->projects;
    }

    /**
     * Set eventtypes
     *
     * @param MongoBundle\Document\EventType $eventtypes
     * @return self
     */
    public function setEventtypes(\MongoBundle\Document\EventType $eventtypes)
    {
        $this->eventtypes = $eventtypes;
        return $this;
    }

    /**
     * Get eventtypes
     *
     * @return MongoBundle\Document\EventType $eventtypes
     */
    public function getEventtypes()
    {
        return $this->eventtypes;
    }

    /**
     * Set creatorUser
     *
     * @param MongoBundle\Document\User $creatorUser
     * @return self
     */
    public function setCreatorUser(\MongoBundle\Document\User $creatorUser)
    {
        $this->creator_user = $creatorUser;
        return $this;
    }

    /**
     * Get creatorUser
     *
     * @return MongoBundle\Document\User $creatorUser
     */
    public function getCreatorUser()
    {
        return $this->creator_user;
    }

    /**
     * Add user
     *
     * @param MongoBundle\Document\User $user
     */
    public function addUser(\MongoBundle\Document\User $user)
    {
        $this->users[] = $user;
    }

    /**
     * Remove user
     *
     * @param MongoBundle\Document\User $user
     */
    public function removeUser(\MongoBundle\Document\User $user)
    {
        $this->users->removeElement($user);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection $users
     */
    public function getUsers()
    {
        return $this->users;
    }
}
