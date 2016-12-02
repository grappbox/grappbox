<?php

namespace SQLBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Project
 */
class Project
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $logo;

    /**
     * @var \DateTime
     */
    private $logoDate;

    /**
     * @var string
     */
    private $phone;

    /**
     * @var string
     */
    private $company;

    /**
     * @var string
     */
    private $contactEmail;

    /**
     * @var string
     */
    private $facebook;

    /**
     * @var string
     */
    private $twitter;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var \DateTime
     */
    private $deletedAt;

    /**
     * @var string
     */
    private $safePassword;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $tasks;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $bugs;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $timelines;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $events;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $whiteboards;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $roles;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $gantts;

    /**
     * @var \SQLBundle\Entity\User
     */
    private $creator_user;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $users;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $customers_access;

    /**
     * @var string
     */
    private $color;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $colors;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $bugtracker_tags;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $tags;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $statProjectAdvancement;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $statLateTasks;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $statBugsEvolution;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $statBugsTagsRepartition;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $statBugAssignationTracker;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $statBugsUsersRepartition;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $statTasksRepartition;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $statUserWorkingCharge;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $statUserTasksAdvancement;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $statStorageSize;

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
     * @return Project
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
     * Set description
     *
     * @param string $description
     * @return Project
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
     * Set logo
     *
     * @param string $logo
     * @return Project
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;

        return $this;
    }

    /**
     * Get logo
     *
     * @return string
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * Set logoDate
     *
     * @param \DateTime $logoDate
     * @return Project
     */
    public function setLogoDate($logoDate)
    {
        $this->logoDate = $logoDate;

        return $this;
    }

    /**
     * Get logoDate
     *
     * @return \DateTime
     */
    public function getLogoDate()
    {
        return $this->logoDate;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return Project
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set company
     *
     * @param string $company
     * @return Project
     */
    public function setCompany($company)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company
     *
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set contactEmail
     *
     * @param string $contactEmail
     * @return Project
     */
    public function setContactEmail($contactEmail)
    {
        $this->contactEmail = $contactEmail;

        return $this;
    }

    /**
     * Get contactEmail
     *
     * @return string
     */
    public function getContactEmail()
    {
        return $this->contactEmail;
    }

    /**
     * Set facebook
     *
     * @param string $facebook
     * @return Project
     */
    public function setFacebook($facebook)
    {
        $this->facebook = $facebook;

        return $this;
    }

    /**
     * Get facebook
     *
     * @return string
     */
    public function getFacebook()
    {
        return $this->facebook;
    }

    /**
     * Set twitter
     *
     * @param string $twitter
     * @return Project
     */
    public function setTwitter($twitter)
    {
        $this->twitter = $twitter;

        return $this;
    }

    /**
     * Get twitter
     *
     * @return string
     */
    public function getTwitter()
    {
        return $this->twitter;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Project
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
     * @return Project
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
     * Set safePassword
     *
     * @param string $safePassword
     * @return Project
     */
    public function setSafePassword($safePassword)
    {
        $this->safePassword = $safePassword;

        return $this;
    }

    /**
     * Get safePassword
     *
     * @return string
     */
    public function getSafePassword()
    {
        return $this->safePassword;
    }

    /**
     * Add tasks
     *
     * @param \SQLBundle\Entity\Task $tasks
     * @return Project
     */
    public function addTask(\SQLBundle\Entity\Task $tasks)
    {
        $this->tasks[] = $tasks;

        return $this;
    }

    /**
     * Remove tasks
     *
     * @param \SQLBundle\Entity\Task $tasks
     */
    public function removeTask(\SQLBundle\Entity\Task $tasks)
    {
        $this->tasks->removeElement($tasks);
    }

    /**
     * Get tasks
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTasks()
    {
        return $this->tasks;
    }

    /**
     * Add bugs
     *
     * @param \SQLBundle\Entity\Bug $bugs
     * @return Project
     */
    public function addBug(\SQLBundle\Entity\Bug $bugs)
    {
        $this->bugs[] = $bugs;

        return $this;
    }

    /**
     * Remove bugs
     *
     * @param \SQLBundle\Entity\Bug $bugs
     */
    public function removeBug(\SQLBundle\Entity\Bug $bugs)
    {
        $this->bugs->removeElement($bugs);
    }

    /**
     * Get bugs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBugs()
    {
        return $this->bugs;
    }

    /**
     * Add timelines
     *
     * @param \SQLBundle\Entity\Timeline $timelines
     * @return Project
     */
    public function addTimeline(\SQLBundle\Entity\Timeline $timelines)
    {
        $this->timelines[] = $timelines;

        return $this;
    }

    /**
     * Remove timelines
     *
     * @param \SQLBundle\Entity\Timeline $timelines
     */
    public function removeTimeline(\SQLBundle\Entity\Timeline $timelines)
    {
        $this->timelines->removeElement($timelines);
    }

    /**
     * Get timelines
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTimelines()
    {
        return $this->timelines;
    }

    /**
     * Add events
     *
     * @param \SQLBundle\Entity\Event $events
     * @return Project
     */
    public function addEvent(\SQLBundle\Entity\Event $events)
    {
        $this->events[] = $events;

        return $this;
    }

    /**
     * Remove events
     *
     * @param \SQLBundle\Entity\Event $events
     */
    public function removeEvent(\SQLBundle\Entity\Event $events)
    {
        $this->events->removeElement($events);
    }

    /**
     * Get events
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * Add whiteboards
     *
     * @param \SQLBundle\Entity\Whiteboard $whiteboards
     * @return Project
     */
    public function addWhiteboard(\SQLBundle\Entity\Whiteboard $whiteboards)
    {
        $this->whiteboards[] = $whiteboards;

        return $this;
    }

    /**
     * Remove whiteboards
     *
     * @param \SQLBundle\Entity\Whiteboard $whiteboards
     */
    public function removeWhiteboard(\SQLBundle\Entity\Whiteboard $whiteboards)
    {
        $this->whiteboards->removeElement($whiteboards);
    }

    /**
     * Get whiteboards
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getWhiteboards()
    {
        return $this->whiteboards;
    }

    /**
     * Add roles
     *
     * @param \SQLBundle\Entity\Role $roles
     * @return Project
     */
    public function addRole(\SQLBundle\Entity\Role $roles)
    {
        $this->roles[] = $roles;

        return $this;
    }

    /**
     * Remove roles
     *
     * @param \SQLBundle\Entity\Role $roles
     */
    public function removeRole(\SQLBundle\Entity\Role $roles)
    {
        $this->roles->removeElement($roles);
    }

    /**
     * Get roles
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Add gantts
     *
     * @param \SQLBundle\Entity\Gantt $gantts
     * @return Project
     */
    public function addGantt(\SQLBundle\Entity\Gantt $gantts)
    {
        $this->gantts[] = $gantts;

        return $this;
    }

    /**
     * Remove gantts
     *
     * @param \SQLBundle\Entity\Gantt $gantts
     */
    public function removeGantt(\SQLBundle\Entity\Gantt $gantts)
    {
        $this->gantts->removeElement($gantts);
    }

    /**
     * Get gantts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGantts()
    {
        return $this->gantts;
    }

    /**
     * Set creator_user
     *
     * @param \SQLBundle\Entity\User $creatorUser
     * @return Project
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
     * @return Project
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
     * Add customers_access
     *
     * @param \SQLBundle\Entity\CustomerAccess $customersAccess
     * @return Project
     */
    public function addCustomersAccess(\SQLBundle\Entity\CustomerAccess $customersAccess)
    {
        $this->customers_access[] = $customersAccess;

        return $this;
    }

    /**
     * Remove customers_access
     *
     * @param \SQLBundle\Entity\CustomerAccess $customersAccess
     */
    public function removeCustomersAccess(\SQLBundle\Entity\CustomerAccess $customersAccess)
    {
        $this->customers_access->removeElement($customersAccess);
    }

    /**
     * Get customers_access
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCustomersAccess()
    {
        return $this->customers_access;
    }

    /**
     * Add tags
     *
     * @param \SQLBundle\Entity\BugtrackerTag $tags
     * @return Project
     */
    public function addBugtrackerTag(\SQLBundle\Entity\BugtrackerTag $tags)
    {
        $this->bugtracker_tags[] = $tags;

        return $this;
    }

    /**
     * Remove tags
     *
     * @param \SQLBundle\Entity\BugtrackerTag $tags
     */
    public function removeBugtrackerTag(\SQLBundle\Entity\BugtrackerTag $tags)
    {
        $this->bugtracker_tags->removeElement($tags);
    }

    /**
     * Get tags
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBugtrackerTags()
    {
        return $this->bugtracker_tags;
    }

    /**
     * Add tags
     *
     * @param \SQLBundle\Entity\Tag $tags
     * @return Project
     */
    public function addTag(\SQLBundle\Entity\Tag $tags)
    {
        $this->tags[] = $tags;

        return $this;
    }

    /**
     * Remove tags
     *
     * @param \SQLBundle\Entity\Tag $tags
     */
    public function removeTag(\SQLBundle\Entity\Tag $tags)
    {
        $this->tags->removeElement($tags);
    }

    /**
     * Get tags
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Set color
     *
     * @param string $color
     * @return Project
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
     * @param \SQLBundle\Entity\Color $colors
     * @return Project
     */
    public function addColor(\SQLBundle\Entity\Color $colors)
    {
        $this->colors[] = $colors;

        return $this;
    }

    /**
     * Remove colors
     *
     * @param \SQLBundle\Entity\Color $colors
     */
    public function removeColor(\SQLBundle\Entity\Color $colors)
    {
        $this->colors->removeElement($colors);
    }

    /**
     * Get colors
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getColors()
    {
        return $this->colors;
    }

    /**
     * Add statProjectAdvancement
     *
     * @param \SQLBundle\Entity\StatProjectAdvancement $statProjectAdvancement
     * @return Project
     */
    public function addStatProjectAdvancement(\SQLBundle\Entity\StatProjectAdvancement $statProjectAdvancement)
    {
        $this->statProjectAdvancement[] = $statProjectAdvancement;

        return $this;
    }

    /**
     * Remove statProjectAdvancement
     *
     * @param \SQLBundle\Entity\StatProjectAdvancement $statProjectAdvancement
     */
    public function removeStatProjectAdvancement(\SQLBundle\Entity\StatProjectAdvancement $statProjectAdvancement)
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
     * @param \SQLBundle\Entity\StatLateTasks $statLateTasks
     * @return Project
     */
    public function addStatLateTasks(\SQLBundle\Entity\StatLateTasks $statLateTasks)
    {
        $this->statLateTasks[] = $statLateTasks;

        return $this;
    }

    /**
     * Remove statLateTasks
     *
     * @param \SQLBundle\Entity\StatLateTasks $statLateTasks
     */
    public function removeStatLateTasks(\SQLBundle\Entity\StatLateTasks $statLateTasks)
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
     * @param \SQLBundle\Entity\StatBugsEvolution $statBugsEvolution
     * @return Project
     */
    public function addStatBugsEvolution(\SQLBundle\Entity\StatBugsEvolution $statBugsEvolution)
    {
        $this->statBugsEvolution[] = $statBugsEvolution;

        return $this;
    }

    /**
     * Remove statBugsEvolution
     *
     * @param \SQLBundle\Entity\StatBugsEvolution $statBugsEvolution
     */
    public function removeStatBugsEvolution(\SQLBundle\Entity\StatBugsEvolution $statBugsEvolution)
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
     * @param \SQLBundle\Entity\StatBugsTagsRepartition $statBugsTagsRepartition
     * @return Project
     */
    public function addStatBugsTagsRepartition(\SQLBundle\Entity\StatBugsTagsRepartition $statBugsTagsRepartition)
    {
        $this->statBugsTagsRepartition[] = $statBugsTagsRepartition;

        return $this;
    }

    /**
     * Remove statBugsTagsRepartition
     *
     * @param \SQLBundle\Entity\StatBugsTagsRepartition $statBugsTagsRepartition
     */
    public function removeStatBugsTagsRepartition(\SQLBundle\Entity\StatBugsTagsRepartition $statBugsTagsRepartition)
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
     * @param \SQLBundle\Entity\StatBugAssignationTracker $statBugAssignationTracker
     * @return Project
     */
    public function addStatBugAssignationTracker(\SQLBundle\Entity\StatBugAssignationTracker $statBugAssignationTracker)
    {
        $this->statBugAssignationTracker[] = $statBugAssignationTracker;

        return $this;
    }

    /**
     * Remove statBugAssignationTracker
     *
     * @param \SQLBundle\Entity\StatBugAssignationTracker $statBugAssignationTracker
     */
    public function removeStatBugAssignationTracker(\SQLBundle\Entity\StatBugAssignationTracker $statBugAssignationTracker)
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
     * @param \SQLBundle\Entity\StatBugsUsersRepartition $statBugsUsersRepartition
     * @return Project
     */
    public function addStatBugsUsersRepartition(\SQLBundle\Entity\StatBugsUsersRepartition $statBugsUsersRepartition)
    {
        $this->statBugsUsersRepartition[] = $statBugsUsersRepartition;

        return $this;
    }

    /**
     * Remove statBugsUsersRepartition
     *
     * @param \SQLBundle\Entity\StatBugsUsersRepartition $statBugsUsersRepartition
     */
    public function removeStatBugsUsersRepartition(\SQLBundle\Entity\StatBugsUsersRepartition $statBugsUsersRepartition)
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
     * @param \SQLBundle\Entity\StatTasksRepartition $statTasksRepartition
     * @return Project
     */
    public function addStatTasksRepartition(\SQLBundle\Entity\StatTasksRepartition $statTasksRepartition)
    {
        $this->statTasksRepartition[] = $statTasksRepartition;

        return $this;
    }

    /**
     * Remove statTasksRepartition
     *
     * @param \SQLBundle\Entity\StatTasksRepartition $statTasksRepartition
     */
    public function removeStatTasksRepartition(\SQLBundle\Entity\StatTasksRepartition $statTasksRepartition)
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
     * @param \SQLBundle\Entity\StatUserWorkingCharge $statUserWorkingCharge
     * @return Project
     */
    public function addStatUserWorkingCharge(\SQLBundle\Entity\StatUserWorkingCharge $statUserWorkingCharge)
    {
        $this->statUserWorkingCharge[] = $statUserWorkingCharge;

        return $this;
    }

    /**
     * Remove statUserWorkingCharge
     *
     * @param \SQLBundle\Entity\StatUserWorkingCharge $statUserWorkingCharge
     */
    public function removeStatUserWorkingCharge(\SQLBundle\Entity\StatUserWorkingCharge $statUserWorkingCharge)
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
     * @param \SQLBundle\Entity\StatUserTasksAdvancement $statUserTasksAdvancement
     * @return Project
     */
    public function addStatUserTasksAdvancement(\SQLBundle\Entity\StatUserTasksAdvancement $statUserTasksAdvancement)
    {
        $this->statUserTasksAdvancement[] = $statUserTasksAdvancement;

        return $this;
    }

    /**
     * Remove statUserTasksAdvancement
     *
     * @param \SQLBundle\Entity\StatUserTasksAdvancement $statUserTasksAdvancement
     */
    public function removeStatUserTasksAdvancement(\SQLBundle\Entity\StatUserTasksAdvancement $statUserTasksAdvancement)
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
     * @param \SQLBundle\Entity\StatStorageSize $statStorageSize
     * @return Project
     */
    public function addStatStorageSize(\SQLBundle\Entity\StatStorageSize $statStorageSize)
    {
        $this->statStorageSize[] = $statStorageSize;

        return $this;
    }

    /**
     * Remove statStorageSize
     *
     * @param \SQLBundle\Entity\StatStorageSize $statStorageSize
     */
    public function removeStatStorageSize(\SQLBundle\Entity\StatStorageSize $statStorageSize)
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
