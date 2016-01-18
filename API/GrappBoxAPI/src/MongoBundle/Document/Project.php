<?php

namespace MongoBundle\Document;



/**
 * MongoBundle\Document\Project
 */
class Project
{
    /**
     * @var $id
     */
    protected $id;

    /**
     * @var string $name
     */
    protected $name;

    /**
     * @var string $description
     */
    protected $description;

    /**
     * @var string $logo
     */
    protected $logo;

    /**
     * @var string $phone
     */
    protected $phone;

    /**
     * @var string $company
     */
    protected $company;

    /**
     * @var string $contactEmail
     */
    protected $contactEmail;

    /**
     * @var string $facebook
     */
    protected $facebook;

    /**
     * @var string $twitter
     */
    protected $twitter;

    /**
     * @var date $createdAt
     */
    protected $createdAt;

    /**
     * @var date $deletedAt
     */
    protected $deletedAt;

    /**
     * @var string $safePassword
     */
    protected $safePassword;

    /**
     * @var MongoBundle\Document\User
     */
    protected $creator_user;

    /**
     * @var MongoBundle\Document\User
     */
    protected $users = array();

    /**
     * @var MongoBundle\Document\Task
     */
    protected $tasks = array();

    /**
     * @var MongoBundle\Document\Bug
     */
    protected $bugs = array();

    /**
     * @var MongoBundle\Document\Timeline
     */
    protected $timelines = array();

    /**
     * @var MongoBundle\Document\Event
     */
    protected $events = array();

    /**
     * @var MongoBundle\Document\Whiteboard
     */
    protected $whiteboards = array();

    /**
     * @var MongoBundle\Document\Role
     */
    protected $roles = array();

    /**
     * @var MongoBundle\Document\Gantt
     */
    protected $gantts = array();

    /**
     * @var MongoBundle\Document\CustomerAccess
     */
    protected $customers_access = array();

    /**
     * @var MongoBundle\Document\Tag
     */
    protected $tags = array();

    public function __construct()
    {
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
        $this->tasks = new \Doctrine\Common\Collections\ArrayCollection();
        $this->bugs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->timelines = new \Doctrine\Common\Collections\ArrayCollection();
        $this->events = new \Doctrine\Common\Collections\ArrayCollection();
        $this->whiteboards = new \Doctrine\Common\Collections\ArrayCollection();
        $this->roles = new \Doctrine\Common\Collections\ArrayCollection();
        $this->gantts = new \Doctrine\Common\Collections\ArrayCollection();
        $this->customers_access = new \Doctrine\Common\Collections\ArrayCollection();
        $this->tags = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set name
     *
     * @param string $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
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
     * Set logo
     *
     * @param string $logo
     * @return self
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;
        return $this;
    }

    /**
     * Get logo
     *
     * @return string $logo
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return self
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * Get phone
     *
     * @return string $phone
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set company
     *
     * @param string $company
     * @return self
     */
    public function setCompany($company)
    {
        $this->company = $company;
        return $this;
    }

    /**
     * Get company
     *
     * @return string $company
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set contactEmail
     *
     * @param string $contactEmail
     * @return self
     */
    public function setContactEmail($contactEmail)
    {
        $this->contactEmail = $contactEmail;
        return $this;
    }

    /**
     * Get contactEmail
     *
     * @return string $contactEmail
     */
    public function getContactEmail()
    {
        return $this->contactEmail;
    }

    /**
     * Set facebook
     *
     * @param string $facebook
     * @return self
     */
    public function setFacebook($facebook)
    {
        $this->facebook = $facebook;
        return $this;
    }

    /**
     * Get facebook
     *
     * @return string $facebook
     */
    public function getFacebook()
    {
        return $this->facebook;
    }

    /**
     * Set twitter
     *
     * @param string $twitter
     * @return self
     */
    public function setTwitter($twitter)
    {
        $this->twitter = $twitter;
        return $this;
    }

    /**
     * Get twitter
     *
     * @return string $twitter
     */
    public function getTwitter()
    {
        return $this->twitter;
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
     * Set safePassword
     *
     * @param string $safePassword
     * @return self
     */
    public function setSafePassword($safePassword)
    {
        $this->safePassword = $safePassword;
        return $this;
    }

    /**
     * Get safePassword
     *
     * @return string $safePassword
     */
    public function getSafePassword()
    {
        return $this->safePassword;
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

    /**
     * Add task
     *
     * @param MongoBundle\Document\Task $task
     */
    public function addTask(\MongoBundle\Document\Task $task)
    {
        $this->tasks[] = $task;
    }

    /**
     * Remove task
     *
     * @param MongoBundle\Document\Task $task
     */
    public function removeTask(\MongoBundle\Document\Task $task)
    {
        $this->tasks->removeElement($task);
    }

    /**
     * Get tasks
     *
     * @return \Doctrine\Common\Collections\Collection $tasks
     */
    public function getTasks()
    {
        return $this->tasks;
    }

    /**
     * Add bug
     *
     * @param MongoBundle\Document\Bug $bug
     */
    public function addBug(\MongoBundle\Document\Bug $bug)
    {
        $this->bugs[] = $bug;
    }

    /**
     * Remove bug
     *
     * @param MongoBundle\Document\Bug $bug
     */
    public function removeBug(\MongoBundle\Document\Bug $bug)
    {
        $this->bugs->removeElement($bug);
    }

    /**
     * Get bugs
     *
     * @return \Doctrine\Common\Collections\Collection $bugs
     */
    public function getBugs()
    {
        return $this->bugs;
    }

    /**
     * Add timeline
     *
     * @param MongoBundle\Document\Timeline $timeline
     */
    public function addTimeline(\MongoBundle\Document\Timeline $timeline)
    {
        $this->timelines[] = $timeline;
    }

    /**
     * Remove timeline
     *
     * @param MongoBundle\Document\Timeline $timeline
     */
    public function removeTimeline(\MongoBundle\Document\Timeline $timeline)
    {
        $this->timelines->removeElement($timeline);
    }

    /**
     * Get timelines
     *
     * @return \Doctrine\Common\Collections\Collection $timelines
     */
    public function getTimelines()
    {
        return $this->timelines;
    }

    /**
     * Add event
     *
     * @param MongoBundle\Document\Event $event
     */
    public function addEvent(\MongoBundle\Document\Event $event)
    {
        $this->events[] = $event;
    }

    /**
     * Remove event
     *
     * @param MongoBundle\Document\Event $event
     */
    public function removeEvent(\MongoBundle\Document\Event $event)
    {
        $this->events->removeElement($event);
    }

    /**
     * Get events
     *
     * @return \Doctrine\Common\Collections\Collection $events
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * Add whiteboard
     *
     * @param MongoBundle\Document\Whiteboard $whiteboard
     */
    public function addWhiteboard(\MongoBundle\Document\Whiteboard $whiteboard)
    {
        $this->whiteboards[] = $whiteboard;
    }

    /**
     * Remove whiteboard
     *
     * @param MongoBundle\Document\Whiteboard $whiteboard
     */
    public function removeWhiteboard(\MongoBundle\Document\Whiteboard $whiteboard)
    {
        $this->whiteboards->removeElement($whiteboard);
    }

    /**
     * Get whiteboards
     *
     * @return \Doctrine\Common\Collections\Collection $whiteboards
     */
    public function getWhiteboards()
    {
        return $this->whiteboards;
    }

    /**
     * Add role
     *
     * @param MongoBundle\Document\Role $role
     */
    public function addRole(\MongoBundle\Document\Role $role)
    {
        $this->roles[] = $role;
    }

    /**
     * Remove role
     *
     * @param MongoBundle\Document\Role $role
     */
    public function removeRole(\MongoBundle\Document\Role $role)
    {
        $this->roles->removeElement($role);
    }

    /**
     * Get roles
     *
     * @return \Doctrine\Common\Collections\Collection $roles
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Add gantt
     *
     * @param MongoBundle\Document\Gantt $gantt
     */
    public function addGantt(\MongoBundle\Document\Gantt $gantt)
    {
        $this->gantts[] = $gantt;
    }

    /**
     * Remove gantt
     *
     * @param MongoBundle\Document\Gantt $gantt
     */
    public function removeGantt(\MongoBundle\Document\Gantt $gantt)
    {
        $this->gantts->removeElement($gantt);
    }

    /**
     * Get gantts
     *
     * @return \Doctrine\Common\Collections\Collection $gantts
     */
    public function getGantts()
    {
        return $this->gantts;
    }

    /**
     * Add customersAccess
     *
     * @param MongoBundle\Document\CustomerAccess $customersAccess
     */
    public function addCustomersAccess(\MongoBundle\Document\CustomerAccess $customersAccess)
    {
        $this->customers_access[] = $customersAccess;
    }

    /**
     * Remove customersAccess
     *
     * @param MongoBundle\Document\CustomerAccess $customersAccess
     */
    public function removeCustomersAccess(\MongoBundle\Document\CustomerAccess $customersAccess)
    {
        $this->customers_access->removeElement($customersAccess);
    }

    /**
     * Get customersAccess
     *
     * @return \Doctrine\Common\Collections\Collection $customersAccess
     */
    public function getCustomersAccess()
    {
        return $this->customers_access;
    }

    /**
     * Add tag
     *
     * @param MongoBundle\Document\Tag $tag
     */
    public function addTag(\MongoBundle\Document\Tag $tag)
    {
        $this->tags[] = $tag;
    }

    /**
     * Remove tag
     *
     * @param MongoBundle\Document\Tag $tag
     */
    public function removeTag(\MongoBundle\Document\Tag $tag)
    {
        $this->tags->removeElement($tag);
    }

    /**
     * Get tags
     *
     * @return \Doctrine\Common\Collections\Collection $tags
     */
    public function getTags()
    {
        return $this->tags;
    }
}
