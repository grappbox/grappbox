<?php

namespace GrappboxBundle\Entity;

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
     * @var \GrappboxBundle\Entity\User
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
    private $statProjectAdvancement;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $statLateTasks;

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
        $this->statProjectAdvancement = new \Doctrine\Common\Collections\ArrayCollection();
        $this->statLateTasks = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @param \GrappboxBundle\Entity\Task $tasks
     * @return Project
     */
    public function addTask(\GrappboxBundle\Entity\Task $tasks)
    {
        $this->tasks[] = $tasks;

        return $this;
    }

    /**
     * Remove tasks
     *
     * @param \GrappboxBundle\Entity\Task $tasks
     */
    public function removeTask(\GrappboxBundle\Entity\Task $tasks)
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
     * @param \GrappboxBundle\Entity\Bug $bugs
     * @return Project
     */
    public function addBug(\GrappboxBundle\Entity\Bug $bugs)
    {
        $this->bugs[] = $bugs;

        return $this;
    }

    /**
     * Remove bugs
     *
     * @param \GrappboxBundle\Entity\Bug $bugs
     */
    public function removeBug(\GrappboxBundle\Entity\Bug $bugs)
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
     * @param \GrappboxBundle\Entity\Timeline $timelines
     * @return Project
     */
    public function addTimeline(\GrappboxBundle\Entity\Timeline $timelines)
    {
        $this->timelines[] = $timelines;

        return $this;
    }

    /**
     * Remove timelines
     *
     * @param \GrappboxBundle\Entity\Timeline $timelines
     */
    public function removeTimeline(\GrappboxBundle\Entity\Timeline $timelines)
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
     * @param \GrappboxBundle\Entity\Event $events
     * @return Project
     */
    public function addEvent(\GrappboxBundle\Entity\Event $events)
    {
        $this->events[] = $events;

        return $this;
    }

    /**
     * Remove events
     *
     * @param \GrappboxBundle\Entity\Event $events
     */
    public function removeEvent(\GrappboxBundle\Entity\Event $events)
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
     * @param \GrappboxBundle\Entity\Whiteboard $whiteboards
     * @return Project
     */
    public function addWhiteboard(\GrappboxBundle\Entity\Whiteboard $whiteboards)
    {
        $this->whiteboards[] = $whiteboards;

        return $this;
    }

    /**
     * Remove whiteboards
     *
     * @param \GrappboxBundle\Entity\Whiteboard $whiteboards
     */
    public function removeWhiteboard(\GrappboxBundle\Entity\Whiteboard $whiteboards)
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
     * @param \GrappboxBundle\Entity\Role $roles
     * @return Project
     */
    public function addRole(\GrappboxBundle\Entity\Role $roles)
    {
        $this->roles[] = $roles;

        return $this;
    }

    /**
     * Remove roles
     *
     * @param \GrappboxBundle\Entity\Role $roles
     */
    public function removeRole(\GrappboxBundle\Entity\Role $roles)
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
     * @param \GrappboxBundle\Entity\Gantt $gantts
     * @return Project
     */
    public function addGantt(\GrappboxBundle\Entity\Gantt $gantts)
    {
        $this->gantts[] = $gantts;

        return $this;
    }

    /**
     * Remove gantts
     *
     * @param \GrappboxBundle\Entity\Gantt $gantts
     */
    public function removeGantt(\GrappboxBundle\Entity\Gantt $gantts)
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
     * @param \GrappboxBundle\Entity\User $creatorUser
     * @return Project
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
     * @return Project
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
     * Add customers_access
     *
     * @param \GrappboxBundle\Entity\CustomerAccess $customersAccess
     * @return Project
     */
    public function addCustomersAccess(\GrappboxBundle\Entity\CustomerAccess $customersAccess)
    {
        $this->customers_access[] = $customersAccess;

        return $this;
    }

    /**
     * Remove customers_access
     *
     * @param \GrappboxBundle\Entity\CustomerAccess $customersAccess
     */
    public function removeCustomersAccess(\GrappboxBundle\Entity\CustomerAccess $customersAccess)
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
     * @var \Doctrine\Common\Collections\Collection
     */
    private $tags;


    /**
     * Add tags
     *
     * @param \GrappboxBundle\Entity\Tag $tags
     * @return Project
     */
    public function addTag(\GrappboxBundle\Entity\Tag $tags)
    {
        $this->tags[] = $tags;

        return $this;
    }

    /**
     * Remove tags
     *
     * @param \GrappboxBundle\Entity\Tag $tags
     */
    public function removeTag(\GrappboxBundle\Entity\Tag $tags)
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
     * @param \GrappboxBundle\Entity\Color $colors
     * @return Project
     */
    public function addColor(\GrappboxBundle\Entity\Color $colors)
    {
        $this->colors[] = $colors;

        return $this;
    }

    /**
     * Remove colors
     *
     * @param \GrappboxBundle\Entity\Color $colors
     */
    public function removeColor(\GrappboxBundle\Entity\Color $colors)
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
     * @param \GrappboxBundle\Entity\StatProjectAdvancement $statProjectAdvancement
     * @return Project
     */
    public function addStatProjectAdvancement(\GrappboxBundle\Entity\StatProjectAdvancement $statProjectAdvancement)
    {
        $this->statProjectAdvancement[] = $statProjectAdvancement;

        return $this;
    }

    /**
     * Remove statProjectAdvancement
     *
     * @param \GrappboxBundle\Entity\StatProjectAdvancement $statProjectAdvancement
     */
    public function removeStatProjectAdvancement(\GrappboxBundle\Entity\StatProjectAdvancement $statProjectAdvancement)
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
     * @param \GrappboxBundle\Entity\StatLateTasks $statLateTasks
     * @return Project
     */
    public function addStatLateTasks(\GrappboxBundle\Entity\StatLateTasks $statLateTasks)
    {
        $this->statLateTasks[] = $statLateTasks;

        return $this;
    }

    /**
     * Remove statLateTasks
     *
     * @param \GrappboxBundle\Entity\StatLateTasks $statLateTasks
     */
    public function removeStatLateTasks(\GrappboxBundle\Entity\StatLateTasks $statLateTasks)
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
}
