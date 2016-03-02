<?php

namespace GrappboxBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Task
 */
class Task
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
    private $dueDate;

    /**
     * @var \DateTime
     */
    private $startedAt;

    /**
     * @var \DateTime
     */
    private $finishedAt;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var \DateTime
     */
    private $deletedAt;

    /**
     * @var boolean
     */
    private $isMilestone;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $ressources;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $dependence;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $task_depended;

    /**
     * @var \GrappboxBundle\Entity\Project
     */
    private $projects;

    /**
     * @var \GrappboxBundle\Entity\User
     */
    private $creator_user;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $tags;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $contains;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->ressources = new \Doctrine\Common\Collections\ArrayCollection();
        $this->dependence = new \Doctrine\Common\Collections\ArrayCollection();
        $this->task_depended = new \Doctrine\Common\Collections\ArrayCollection();
        $this->tags = new \Doctrine\Common\Collections\ArrayCollection();
        $this->contains = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function objectToArray()
    {
      return array(
        'id' => $this->id,
        'creator' => $this->creator_user->getId() ,
        'title' => $this->title ,
        'description' => $this->description ,
        'dueDate' => $this->dueDate ,
        'startedAt' => $this->startedAt ,
        'finishedAt' => $this->finishedAt ,
        'projectId' => $this->projects->getId()
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
     * @return Task
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
     * @return Task
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
     * Set dueDate
     *
     * @param \DateTime $dueDate
     * @return Task
     */
    public function setDueDate($dueDate)
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    /**
     * Get dueDate
     *
     * @return \DateTime 
     */
    public function getDueDate()
    {
        return $this->dueDate;
    }

    /**
     * Set startedAt
     *
     * @param \DateTime $startedAt
     * @return Task
     */
    public function setStartedAt($startedAt)
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    /**
     * Get startedAt
     *
     * @return \DateTime 
     */
    public function getStartedAt()
    {
        return $this->startedAt;
    }

    /**
     * Set finishedAt
     *
     * @param \DateTime $finishedAt
     * @return Task
     */
    public function setFinishedAt($finishedAt)
    {
        $this->finishedAt = $finishedAt;

        return $this;
    }

    /**
     * Get finishedAt
     *
     * @return \DateTime 
     */
    public function getFinishedAt()
    {
        return $this->finishedAt;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Task
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
     * @return Task
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
     * Set isMilestone
     *
     * @param boolean $isMilestone
     * @return Task
     */
    public function setIsMilestone($isMilestone)
    {
        $this->isMilestone = $isMilestone;

        return $this;
    }

    /**
     * Get isMilestone
     *
     * @return boolean 
     */
    public function getIsMilestone()
    {
        return $this->isMilestone;
    }

    /**
     * Add ressources
     *
     * @param \GrappboxBundle\Entity\Ressources $ressources
     * @return Task
     */
    public function addRessource(\GrappboxBundle\Entity\Ressources $ressources)
    {
        $this->ressources[] = $ressources;

        return $this;
    }

    /**
     * Remove ressources
     *
     * @param \GrappboxBundle\Entity\Ressources $ressources
     */
    public function removeRessource(\GrappboxBundle\Entity\Ressources $ressources)
    {
        $this->ressources->removeElement($ressources);
    }

    /**
     * Get ressources
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRessources()
    {
        return $this->ressources;
    }

    /**
     * Add dependence
     *
     * @param \GrappboxBundle\Entity\Dependencies $dependence
     * @return Task
     */
    public function addDependence(\GrappboxBundle\Entity\Dependencies $dependence)
    {
        $this->dependence[] = $dependence;

        return $this;
    }

    /**
     * Remove dependence
     *
     * @param \GrappboxBundle\Entity\Dependencies $dependence
     */
    public function removeDependence(\GrappboxBundle\Entity\Dependencies $dependence)
    {
        $this->dependence->removeElement($dependence);
    }

    /**
     * Get dependence
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDependence()
    {
        return $this->dependence;
    }

    /**
     * Add task_depended
     *
     * @param \GrappboxBundle\Entity\Dependencies $taskDepended
     * @return Task
     */
    public function addTaskDepended(\GrappboxBundle\Entity\Dependencies $taskDepended)
    {
        $this->task_depended[] = $taskDepended;

        return $this;
    }

    /**
     * Remove task_depended
     *
     * @param \GrappboxBundle\Entity\Dependencies $taskDepended
     */
    public function removeTaskDepended(\GrappboxBundle\Entity\Dependencies $taskDepended)
    {
        $this->task_depended->removeElement($taskDepended);
    }

    /**
     * Get task_depended
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTaskDepended()
    {
        return $this->task_depended;
    }

    /**
     * Set projects
     *
     * @param \GrappboxBundle\Entity\Project $projects
     * @return Task
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
     * Set creator_user
     *
     * @param \GrappboxBundle\Entity\User $creatorUser
     * @return Task
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
     * Add tags
     *
     * @param \GrappboxBundle\Entity\Tag $tags
     * @return Task
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
     * Add contains
     *
     * @param \GrappboxBundle\Entity\Contains $contains
     * @return Task
     */
    public function addContain(\GrappboxBundle\Entity\Contains $contains)
    {
        $this->contains[] = $contains;

        return $this;
    }

    /**
     * Remove contains
     *
     * @param \GrappboxBundle\Entity\Contains $contains
     */
    public function removeContain(\GrappboxBundle\Entity\Contains $contains)
    {
        $this->contains->removeElement($contains);
    }

    /**
     * Get contains
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getContains()
    {
        return $this->contains;
    }
}
