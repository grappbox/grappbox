<?php

namespace GrappboxBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Task
 */
class Task
{
    public function objectToArray()
    {
      return array(
        'id' => $this->id,
        'creator' => $this->creator_user->getId() ,
        'title' => $this->title ,
        'description' => $this->description ,
        'color' => $this->color ,
        'dueDate' => $this->dueDate ,
        'startedAt' => $this->startedAt ,
        'finishedAt' => $this->finishedAt ,
        'projectId' => $this->projects->getId()
      );
    }

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
    private $color;

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
     * @var boolean
     */
    private $isContainer;

    /**
     * @var integer
     */
    private $advance;

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
     * @var \Doctrine\Common\Collections\Collection
     */
    private $tasks_container;
    
    /**
     * @var \GrappboxBundle\Entity\Task
     */
    private $container;

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
     * Constructor
     */
    public function __construct()
    {
        $this->ressources = new \Doctrine\Common\Collections\ArrayCollection();
        $this->dependence = new \Doctrine\Common\Collections\ArrayCollection();
        $this->task_depended = new \Doctrine\Common\Collections\ArrayCollection();
        $this->tasks_container = new \Doctrine\Common\Collections\ArrayCollection();
        $this->tags = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set color
     *
     * @param string $color
     * @return Task
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
     * Set isContainer
     *
     * @param boolean $isContainer
     * @return Task
     */
    public function setIsContainer($isContainer)
    {
        $this->isContainer = $isContainer;

        return $this;
    }

    /**
     * Get isContainer
     *
     * @return boolean 
     */
    public function getIsContainer()
    {
        return $this->isContainer;
    }

    /**
     * Set advance
     *
     * @param integer $advance
     * @return Task
     */
    public function setAdvance($advance)
    {
        $this->advance = $advance;

        return $this;
    }

    /**
     * Get advance
     *
     * @return integer 
     */
    public function getAdvance()
    {
        return $this->advance;
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
     * Add tasks_container
     *
     * @param \GrappboxBundle\Entity\Task $tasksContainer
     * @return Task
     */
    public function addTasksContainer(\GrappboxBundle\Entity\Task $tasksContainer)
    {
        $this->tasks_container[] = $tasksContainer;

        return $this;
    }

    /**
     * Remove tasks_container
     *
     * @param \GrappboxBundle\Entity\Task $tasksContainer
     */
    public function removeTasksContainer(\GrappboxBundle\Entity\Task $tasksContainer)
    {
        $this->tasks_container->removeElement($tasksContainer);
    }

    /**
     * Get tasks_container
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTasksContainer()
    {
        return $this->tasks_container;
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
     * Set container
     *
     * @param \GrappboxBundle\Entity\Task $container
     * @return Task
     */
    public function setContainer(\GrappboxBundle\Entity\Task $container = null)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Get container
     *
     * @return \GrappboxBundle\Entity\Task 
     */
    public function getContainer()
    {
        return $this->container;
    }
}
