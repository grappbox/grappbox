<?php

namespace APIBundle\Entity;

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
     * @var \APIBundle\Entity\User
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
        $this->tasks = new \Doctrine\Common\Collections\ArrayCollection();
        $this->bugs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->timelines = new \Doctrine\Common\Collections\ArrayCollection();
        $this->events = new \Doctrine\Common\Collections\ArrayCollection();
        $this->whiteboards = new \Doctrine\Common\Collections\ArrayCollection();
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @param \APIBundle\Entity\Task $tasks
     * @return Project
     */
    public function addTask(\APIBundle\Entity\Task $tasks)
    {
        $this->tasks[] = $tasks;

        return $this;
    }

    /**
     * Remove tasks
     *
     * @param \APIBundle\Entity\Task $tasks
     */
    public function removeTask(\APIBundle\Entity\Task $tasks)
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
     * @param \APIBundle\Entity\Bug $bugs
     * @return Project
     */
    public function addBug(\APIBundle\Entity\Bug $bugs)
    {
        $this->bugs[] = $bugs;

        return $this;
    }

    /**
     * Remove bugs
     *
     * @param \APIBundle\Entity\Bug $bugs
     */
    public function removeBug(\APIBundle\Entity\Bug $bugs)
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
     * @param \APIBundle\Entity\Timeline $timelines
     * @return Project
     */
    public function addTimeline(\APIBundle\Entity\Timeline $timelines)
    {
        $this->timelines[] = $timelines;

        return $this;
    }

    /**
     * Remove timelines
     *
     * @param \APIBundle\Entity\Timeline $timelines
     */
    public function removeTimeline(\APIBundle\Entity\Timeline $timelines)
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
     * @param \APIBundle\Entity\Event $events
     * @return Project
     */
    public function addEvent(\APIBundle\Entity\Event $events)
    {
        $this->events[] = $events;

        return $this;
    }

    /**
     * Remove events
     *
     * @param \APIBundle\Entity\Event $events
     */
    public function removeEvent(\APIBundle\Entity\Event $events)
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
     * @param \APIBundle\Entity\Whiteboard $whiteboards
     * @return Project
     */
    public function addWhiteboard(\APIBundle\Entity\Whiteboard $whiteboards)
    {
        $this->whiteboards[] = $whiteboards;

        return $this;
    }

    /**
     * Remove whiteboards
     *
     * @param \APIBundle\Entity\Whiteboard $whiteboards
     */
    public function removeWhiteboard(\APIBundle\Entity\Whiteboard $whiteboards)
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
     * Set creator_user
     *
     * @param \APIBundle\Entity\User $creatorUser
     * @return Project
     */
    public function setCreatorUser(\APIBundle\Entity\User $creatorUser = null)
    {
        $this->creator_user = $creatorUser;

        return $this;
    }

    /**
     * Get creator_user
     *
     * @return \APIBundle\Entity\User 
     */
    public function getCreatorUser()
    {
        return $this->creator_user;
    }

    /**
     * Add users
     *
     * @param \APIBundle\Entity\User $users
     * @return Project
     */
    public function addUser(\APIBundle\Entity\User $users)
    {
        $this->users[] = $users;

        return $this;
    }

    /**
     * Remove users
     *
     * @param \APIBundle\Entity\User $users
     */
    public function removeUser(\APIBundle\Entity\User $users)
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
}
