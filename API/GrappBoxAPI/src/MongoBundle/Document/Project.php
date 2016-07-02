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
     * @var string $color
     */
    protected $color;

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
     * @var MongoBundle\Document\User
     */
    protected $creator_user;

    /**
     * @var MongoBundle\Document\User
     */
    protected $users = array();

    /**
     * @var MongoBundle\Document\CustomerAccess
     */
    protected $customers_access = array();

    /**
     * @var MongoBundle\Document\Color
     */
    protected $colors = array();

    /**
     * @var MongoBundle\Document\Tag
     */
    protected $tags = array();

    /**
     * @var MongoBundle\Document\StatProjectAdvancement
     */
    protected $statProjectAdvancement;

    /**
     * @var MongoBundle\Document\StatLateTasks
     */
    protected $statLateTasks;

    /**
     * @var MongoBundle\Document\StatBugsEvolution
     */
    protected $statBugsEvolution;

    /**
     * @var MongoBundle\Document\StatBugsTagsRepartition
     */
    protected $statBugsTagsRepartition;

    /**
     * @var MongoBundle\Document\StatBugAssignationTracker
     */
    protected $statBugAssignationTracker;

    /**
     * @var MongoBundle\Document\StatBugsUsersRepartition
     */
    protected $statBugsUsersRepartition;

    /**
     * @var MongoBundle\Document\StatTasksRepartition
     */
    protected $statTasksRepartition;

    /**
     * @var MongoBundle\Document\StatUserWorkingCharge
     */
    protected $statUserWorkingCharge;

    /**
     * @var MongoBundle\Document\StatUserTasksAdvancement
     */
    protected $statUserTasksAdvancement;

    /**
     * @var MongoBundle\Document\StatStorageSize
     */
    protected $statStorageSize;


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
        $this->colors = new \Doctrine\Common\Collections\ArrayCollection();
        $this->statProjectAdvancement = new \Doctrine\Common\Collections\ArrayCollection();
        $this->statLateTasks = new \Doctrine\Common\Collections\ArrayCollection();
        $this->statBugsEvolution = new \Doctrine\Common\Collections\ArrayCollection();
        $this->statBugsTagsRepartition = new \Doctrine\Common\Collections\ArrayCollection();
        $this->statBugAssignationTracker = new \Doctrine\Common\Collections\ArrayCollection();
        $this->statBugsUsersRepartition = new \Doctrine\Common\Collections\ArrayCollection();
        $this->statTasksRepartition = new \Doctrine\Common\Collections\ArrayCollection();
        $this->statUserWorkingCharge = new \Doctrine\Common\Collections\ArrayCollection();
        $this->statUserTasksAdvancement = new \Doctrine\Common\Collections\ArrayCollection();
        $this->statStorageSize = new \Doctrine\Common\Collections\ArrayCollection();
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

    /**
     * Set color
     *
     * @param string $color
     * @return self
     */
    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Get color
     *
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * Add colors
     *
     * @param MongoBundle\Document\Color $colors
     */
    public function addColor(\MongoBundle\Document\Color $colors)
    {
        $this->colors[] = $colors;

        return $this;
    }

    /**
     * Remove colors
     *
     * @param  MongoBundle\Document\Color $colors
     */
    public function removeColor(\MongoBundle\Document\Color $colors)
    {
        $this->colors->removeElement($colors);
    }

    /**
     * Get colors
     *
     * @return \Doctrine\Common\Collections\Collection $colors
     */
    public function getColors()
    {
        return $this->colors;
    }

    /**
     * Add statProjectAdvancement
     *
     * @param MongoBundle\Document\StatProjectAdvancement $statProjectAdvancement
     */
    public function addStatProjectAdvancement(\MongoBundle\Document\StatProjectAdvancement $statProjectAdvancement)
    {
        $this->statProjectAdvancement[] = $statProjectAdvancement;

        return $this;
    }

    /**
     * Remove statProjectAdvancement
     *
     * @param MongoBundle\Document\StatProjectAdvancement $statProjectAdvancement
     */
    public function removeStatProjectAdvancement(\MongoBundle\Document\StatProjectAdvancement $statProjectAdvancement)
    {
        $this->statProjectAdvancement->removeElement($statProjectAdvancement);
    }

    /**
     * Get statProjectAdvancement
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getStatProjectAdvancement()
    {
        return $this->StatProjectAdvancement;
    }

    /**
     * Add statLateTasks
     *
     * @param MongoBundle\Document\StatLateTasks $statLateTasks
     */
    public function addStatLateTasks(\MongoBundle\Document\StatLateTasks $statLateTasks)
    {
        $this->statLateTasks[] = $statLateTasks;

        return $this;
    }

    /**
     * Remove statLateTasks
     *
     * @param \GrappboxBundle\Entity\StatLateTasks $statLateTasks
     */
    public function removeStatLateTasks(\MongoBundle\Document\StatLateTasks $statLateTasks)
    {
        $this->statLateTasks->removeElement($statLateTasks);
    }

    /**
     * Get statLateTasks
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getStatLateTasks()
    {
        return $this->StatLateTasks;
    }

    /**
     * Add statBugsEvolution
     *
     * @param MongoBundle\Document\StatBugsEvolution $statBugsEvolution
     */
    public function addStatBugsEvolution(\MongoBundle\Document\StatBugsEvolution $statBugsEvolution)
    {
        $this->statBugsEvolution[] = $statBugsEvolution;

        return $this;
    }

    /**
     * Remove statBugsEvolution
     *
     * @param MongoBundle\Document\StatBugsEvolution $statBugsEvolution
     */
    public function removeStatBugsEvolution(\MongoBundle\Document\StatBugsEvolution $statBugsEvolution)
    {
        $this->statBugsEvolution->removeElement($statBugsEvolution);
    }

    /**
     * Get statBugsEvolution
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getStatBugsEvolution()
    {
        return $this->statBugsEvolution;
    }

    /**
     * Add statBugsTagsRepartition
     *
     * @param MongoBundle\Document\StatBugsTagsRepartition $statBugsTagsRepartition
     */
    public function addStatBugsTagsRepartition(\MongoBundle\Document\StatBugsTagsRepartition $statBugsTagsRepartition)
    {
        $this->statBugsTagsRepartition[] = $statBugsTagsRepartition;

        return $this;
    }

    /**
     * Remove statBugsTagsRepartition
     *
     * @param MongoBundle\Document\StatBugsTagsRepartition $statBugsTagsRepartition
     */
    public function removeStatBugsTagsRepartition(\MongoBundle\Document\StatBugsTagsRepartition $statBugsTagsRepartition)
    {
        $this->statBugsTagsRepartition->removeElement($statBugsTagsRepartition);
    }

    /**
     * Get statBugsTagsRepartition
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getStatBugsTagsRepartition()
    {
        return $this->statBugsTagsRepartition;
    }

    /**
     * Add statBugAssignationTracker
     *
     * @param MongoBundle\Document\tatBugAssignationTracker $statBugAssignationTracker
     */
    public function addStatBugAssignationTracker(\MongoBundle\Document\StatBugAssignationTracker $statBugAssignationTracker)
    {
        $this->statBugAssignationTracker[] = $statBugAssignationTracker;

        return $this;
    }

    /**
     * Remove statBugAssignationTracker
     *
     * @param MongoBundle\Document\StatBugAssignationTracker $statBugAssignationTracker
     */
    public function removeStatBugAssignationTracker(\MongoBundle\Document\StatBugAssignationTracker $statBugAssignationTracker)
    {
        $this->statBugAssignationTracker->removeElement($statBugAssignationTracker);
    }

    /**
     * Get statBugAssignationTracker
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getStatBugAssignationTracker()
    {
        return $this->statBugAssignationTracker;
    }

    /**
     * Add statBugsUsersRepartition
     *
     * @param MongoBundle\Document\tatBugsUsersRepartition $statBugsUsersRepartition
     */
    public function addStatBugsUsersRepartition(\MongoBundle\Document\StatBugsUsersRepartition $statBugsUsersRepartition)
    {
        $this->statBugsUsersRepartition[] = $statBugsUsersRepartition;

        return $this;
    }

    /**
     * Remove statBugsUsersRepartition
     *
     * @param MongoBundle\Document\StatBugsUsersRepartition $statBugsUsersRepartition
     */
    public function removeStatBugsUsersRepartition(\MongoBundle\Document\StatBugsUsersRepartition $statBugsUsersRepartition)
    {
        $this->statBugsUsersRepartition->removeElement($statBugsUsersRepartition);
    }

    /**
     * Get statBugsUsersRepartition
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getStatBugsUsersRepartition()
    {
        return $this->statBugsUsersRepartition;
    }

    /**
     * Add statTasksRepartition
     *
     * @param MongoBundle\Document\StatTasksRepartition $statTasksRepartition
     */
    public function addStatTasksRepartition(\MongoBundle\Document\StatTasksRepartition $statTasksRepartition)
    {
        $this->statTasksRepartition[] = $statTasksRepartition;

        return $this;
    }

    /**
     * Remove statTasksRepartition
     *
     * @param MongoBundle\Document\StatTasksRepartition $statTasksRepartition
     */
    public function removeStatTasksRepartition(\MongoBundle\Document\StatTasksRepartition $statTasksRepartition)
    {
        $this->statTasksRepartition->removeElement($statTasksRepartition);
    }

    /**
     * Get statTasksRepartition
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getStatTasksRepartition()
    {
        return $this->statTasksRepartition;
    }

    /**
     * Add statUserWorkingCharge
     *
     * @param MongoBundle\Document\StatUserWorkingCharge $statUserWorkingCharge
     */
    public function addStatUserWorkingCharge(\MongoBundle\Document\StatUserWorkingCharge $statUserWorkingCharge)
    {
        $this->statUserWorkingCharge[] = $statUserWorkingCharge;

        return $this;
    }

    /**
     * Remove statUserWorkingCharge
     *
     * @param MongoBundle\Document\StatUserWorkingCharge $statUserWorkingCharge
     */
    public function removeStatUserWorkingCharge(\MongoBundle\Document\StatUserWorkingCharge $statUserWorkingCharge)
    {
        $this->statUserWorkingCharge->removeElement($statUserWorkingCharge);
    }

    /**
     * Get statUserWorkingCharge
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getStatUserWorkingCharge()
    {
        return $this->statUserWorkingCharge;
    }

    /**
     * Add statUserTasksAdvancement
     *
     * @param MongoBundle\Document\StatUserTasksAdvancement $statUserTasksAdvancement
     */
    public function addStatUserTasksAdvancement(\MongoBundle\Document\StatUserTasksAdvancement $statUserTasksAdvancement)
    {
        $this->statUserTasksAdvancement[] = $statUserTasksAdvancement;

        return $this;
    }

    /**
     * Remove statUserTasksAdvancement
     *
     * @param MongoBundle\Document\StatUserTasksAdvancement $statUserTasksAdvancement
     */
    public function removeStatUserTasksAdvancement(\MongoBundle\Document\StatUserTasksAdvancement $statUserTasksAdvancement)
    {
        $this->statUserTasksAdvancement->removeElement($statUserTasksAdvancement);
    }

    /**
     * Get statUserTasksAdvancement
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getStatUserTasksAdvancement()
    {
        return $this->statUserTasksAdvancement;
    }

    /**
     * Add statStorageSize
     *
     * @param MongoBundle\Document\StatStorageSize $statStorageSize
     */
    public function addStatStorageSize(\MongoBundle\Document\StatStorageSize $statStorageSize)
    {
        $this->statStorageSize[] = $statStorageSize;

        return $this;
    }

    /**
     * Remove statStorageSize
     *
     * @param MongoBundle\Document\StatStorageSize $statStorageSize
     */
    public function removeStatStorageSize(\MongoBundle\Document\StatStorageSize $statStorageSize)
    {
        $this->statStorageSize->removeElement($statStorageSize);
    }

    /**
     * Get statStorageSize
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getStatStorageSize()
    {
        return $this->statStorageSize;
    }
}
