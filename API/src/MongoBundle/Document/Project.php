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
     * @var date $logoDate
     */
    protected $logoDate;

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
     * @var string $color
     */
    protected $color;

    /**
     * @var MongoBundle\Document\Color
     */
    protected $colors = array();

    /**
     * @var MongoBundle\Document\BugtrackerTag
     */
    protected $bugtracker_tags = array();

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

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->tasks = new \Doctrine\Common\Collections\ArrayCollection();
        $this->bugs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->timelines = new \Doctrine\Common\Collections\ArrayCollection();
        $this->events = new \Doctrine\Common\Collections\ArrayCollection();
        $this->whiteboards = new \Doctrine\Common\Collections\ArrayCollection();
        $this->roles = new \Doctrine\Common\Collections\ArrayCollection();
        $this->gantts = new \Doctrine\Common\Collections\ArrayCollection();
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
        $this->tags = new \Doctrine\Common\Collections\ArrayCollection();
        $this->bugtracker_tags = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Get object content into array
     *
     * @return array
     */
    public function objectToArray($em, $user)
    {
        $color = $em->getRepository('SQLBundle:Color')->findOneBy(array("project" => $this, "user" => $user));
        if ($color === null)
            $color = $this->getColor();
        else
            $color = $color->getColor();
        $creator = array("id" => $this->creator_user->getId(), "firstname" => $this->creator_user->getFirstname(), "lastname" => $this->creator_user->getLastname());
        return array(
            "id" => $this->id,
            "name" => $this->name,
            "description" => $this->description,
            "creator" => $creator,
            "logo" => $this->logoDate ? $this->logoDate->format('Y-m-d H:i:s') : null,
            "phone" => $this->phone,
            "company" => $this->company,
            "contact_mail" => $this->contactEmail,
            "facebook" => $this->facebook,
            "twitter" => $this->twitter,
            "color" => $color,
            "created_at" => $this->createdAt ? $this->createdAt->format('Y-m-d H:i:s') : null,
            "deleted_at" => $this->deletedAt ? $this->deletedAt->format('Y-m-d H:i:s') : null
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
     * Set logoDate
     *
     * @param date $logoDate
     * @return self
     */
    public function setLogoDate($logoDate)
    {
        $this->logoDate = $logoDate;
        return $this;
    }

    /**
     * Get logoDate
     *
     * @return date
     */
    public function getLogoDate()
    {
        return $this->logoDate;
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
     * @return self
     */
    public function addTask( $task)
    {
        $this->tasks[] = $task;
        return $this;
    }

    /**
     * Remove task
     *
     * @param MongoBundle\Document\Task $task
     */
    public function removeTask( $task)
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
     * @return self
     */
    public function addBug( $bug)
    {
        $this->bugs[] = $bug;
        return $this;
    }

    /**
     * Remove bug
     *
     * @param MongoBundle\Document\Bug $bug
     */
    public function removeBug( $bug)
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
     * @return self
     */
    public function addTimeline( $timeline)
    {
        $this->timelines[] = $timeline;
        return $this;
    }

    /**
     * Remove timeline
     *
     * @param MongoBundle\Document\Timeline $timeline
     */
    public function removeTimeline( $timeline)
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
     * @return self
     */
    public function addEvent( $event)
    {
        $this->events[] = $event;
        return $this;
    }

    /**
     * Remove event
     *
     * @param MongoBundle\Document\Event $event
     */
    public function removeEvent( $event)
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
     * @return self
     */
    public function addWhiteboard($whiteboard)
    {
        $this->whiteboards[] = $whiteboard;
        return $this;
    }

    /**
     * Remove whiteboard
     *
     * @param MongoBundle\Document\Whiteboard $whiteboard
     */
    public function removeWhiteboard($whiteboard)
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
     * @return self
     */
    public function addRole( $role)
    {
        $this->roles[] = $role;
        return $this;
    }

    /**
     * Remove role
     *
     * @param MongoBundle\Document\Role $role
     */
    public function removeRole( $role)
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
     * @return self
     */
    public function addGantt( $gantt)
    {
        $this->gantts[] = $gantt;
        return $this;
    }

    /**
     * Remove gantt
     *
     * @param MongoBundle\Document\Gantt $gantt
     */
    public function removeGantt( $gantt)
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
    public function setCreatorUser( $creatorUser)
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
     * @return self
     */
    public function addUser( $user)
    {
        $this->users[] = $user;
        return $this;
    }

    /**
     * Remove user
     *
     * @param MongoBundle\Document\User $user
     */
    public function removeUser( $user)
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
     * @return self
     */
    public function addCustomersAccess( $customersAccess)
    {
        $this->customers_access[] = $customersAccess;
        return $this;
    }

    /**
     * Remove customersAccess
     *
     * @param MongoBundle\Document\CustomerAccess $customersAccess
     */
    public function removeCustomersAccess( $customersAccess)
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
     * @return self
     */
    public function addBugtrackerTag( $tag)
    {
        $this->bugtracker_tags[] = $tag;
        return $this;
    }

    /**
     * Remove tag
     *
     * @param MongoBundle\Document\Tag $tag
     */
    public function removeBugtrackerTag( $tag)
    {
        $this->bugtracker_tags->removeElement($tag);
    }

    /**
     * Get tags
     *
     * @return \Doctrine\Common\Collections\Collection $tags
     */
    public function getBugtrackerTags()
    {
        return $this->bugtracker_tags;
    }

    /**
     * Add tag
     *
     * @param MongoBundle\Document\Tag $tag
     * @return self
     */
    public function addTag( $tag)
    {
        $this->tags[] = $tag;
        return $this;
    }

    /**
     * Remove tag
     *
     * @param MongoBundle\Document\Tag $tag
     */
    public function removeTag( $tag)
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
    public function addColor( $colors)
    {
        $this->colors[] = $colors;
        return $this;
    }

    /**
     * Remove colors
     *
     * @param  MongoBundle\Document\Color $colors
     */
    public function removeColor( $colors)
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
     * @return self
     */
    public function addStatProjectAdvancement( $statProjectAdvancement)
    {
        $this->statProjectAdvancement[] = $statProjectAdvancement;
        return $this;
    }

    /**
     * Remove statProjectAdvancement
     *
     * @param MongoBundle\Document\StatProjectAdvancement $statProjectAdvancement
     */
    public function removeStatProjectAdvancement( $statProjectAdvancement)
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
     * @return self
     */
    public function addStatLateTasks( $statLateTasks)
    {
        $this->statLateTasks[] = $statLateTasks;
        return $this;
    }

    /**
     * Remove statLateTasks
     *
     * @param MongoBundle\Document\StatLateTasks $statLateTasks
     */
    public function removeStatLateTasks( $statLateTasks)
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
     * @return self
     */
    public function addStatBugsEvolution( $statBugsEvolution)
    {
        $this->statBugsEvolution[] = $statBugsEvolution;
        return $this;
    }

    /**
     * Remove statBugsEvolution
     *
     * @param MongoBundle\Document\StatBugsEvolution $statBugsEvolution
     */
    public function removeStatBugsEvolution( $statBugsEvolution)
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
     * @return self
     */
    public function addStatBugsTagsRepartition( $statBugsTagsRepartition)
    {
        $this->statBugsTagsRepartition[] = $statBugsTagsRepartition;
        return $this;
    }

    /**
     * Remove statBugsTagsRepartition
     *
     * @param MongoBundle\Document\StatBugsTagsRepartition $statBugsTagsRepartition
     */
    public function removeStatBugsTagsRepartition( $statBugsTagsRepartition)
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
     * @return self
     */
    public function addStatBugAssignationTracker( $statBugAssignationTracker)
    {
        $this->statBugAssignationTracker[] = $statBugAssignationTracker;
        return $this;
    }

    /**
     * Remove statBugAssignationTracker
     *
     * @param MongoBundle\Document\StatBugAssignationTracker $statBugAssignationTracker
     */
    public function removeStatBugAssignationTracker( $statBugAssignationTracker)
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
     * @return self
     */
    public function addStatBugsUsersRepartition( $statBugsUsersRepartition)
    {
        $this->statBugsUsersRepartition[] = $statBugsUsersRepartition;
        return $this;
    }

    /**
     * Remove statBugsUsersRepartition
     *
     * @param MongoBundle\Document\StatBugsUsersRepartition $statBugsUsersRepartition
     */
    public function removeStatBugsUsersRepartition( $statBugsUsersRepartition)
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
     * @return self
     */
    public function addStatTasksRepartition( $statTasksRepartition)
    {
        $this->statTasksRepartition[] = $statTasksRepartition;
        return $this;
    }

    /**
     * Remove statTasksRepartition
     *
     * @param MongoBundle\Document\StatTasksRepartition $statTasksRepartition
     */
    public function removeStatTasksRepartition( $statTasksRepartition)
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
     * @return self
     */
    public function addStatUserWorkingCharge( $statUserWorkingCharge)
    {
        $this->statUserWorkingCharge[] = $statUserWorkingCharge;
        return $this;
    }

    /**
     * Remove statUserWorkingCharge
     *
     * @param MongoBundle\Document\StatUserWorkingCharge $statUserWorkingCharge
     */
    public function removeStatUserWorkingCharge( $statUserWorkingCharge)
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
     * @return self
     */
    public function addStatUserTasksAdvancement( $statUserTasksAdvancement)
    {
        $this->statUserTasksAdvancement[] = $statUserTasksAdvancement;
        return $this;
    }

    /**
     * Remove statUserTasksAdvancement
     *
     * @param MongoBundle\Document\StatUserTasksAdvancement $statUserTasksAdvancement
     */
    public function removeStatUserTasksAdvancement( $statUserTasksAdvancement)
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
     * @return self
     */
    public function addStatStorageSize( $statStorageSize)
    {
        $this->statStorageSize[] = $statStorageSize;
        return $this;
    }

    /**
     * Remove statStorageSize
     *
     * @param MongoBundle\Document\StatStorageSize $statStorageSize
     */
    public function removeStatStorageSize( $statStorageSize)
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

    /**
     * Add statLateTask
     *
     * @param MongoBundle\Document\StatLateTasks $statLateTask
     */
    public function addStatLateTask(\MongoBundle\Document\StatLateTasks $statLateTask)
    {
        $this->statLateTasks[] = $statLateTask;
    }

    /**
     * Remove statLateTask
     *
     * @param MongoBundle\Document\StatLateTasks $statLateTask
     */
    public function removeStatLateTask(\MongoBundle\Document\StatLateTasks $statLateTask)
    {
        $this->statLateTasks->removeElement($statLateTask);
    }
}
