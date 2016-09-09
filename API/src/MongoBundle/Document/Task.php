<?php

namespace MongoBundle\Document;



/**
 * MongoBundle\Document\Task
 */
class Task
{
  public function objectToArray()
  {
    return array(
      'id' => $this->id,
      'creatorId' => $this->creatorId ,
      'title' => $this->title ,
      'description' => $this->description ,
      'color' => $this->color,
      'dueDate' => $this->dueDate ,
      'startedAt' => $this->startedAt ,
      'finishedAt' => $this->finishedAt ,
      'projectId' => $this->projects->getId(),
    );
  }

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
     * @var string
     */
    private $color;

    /**
     * @var date $dueDate
     */
    protected $dueDate;

    /**
     * @var date $startedAt
     */
    protected $startedAt;

    /**
     * @var date $finishedAt
     */
    protected $finishedAt;

    /**
     * @var date $createdAt
     */
    protected $createdAt;

    /**
     * @var date $deletedAt
     */
    protected $deletedAt;

    /**
     * @var boolean
     */
    protected $isMilestone;

    /**
     * @var boolean
     */
    protected $isContainer;

    /**
     * @var integer
     */
    protected $advance;

    /**
     * @var MongoBundle\Document\Ressources
     */
    protected $ressources;

    /**
     * @var \MongoBundle\Document\Dependencies
     */
    protected $dependence;

    /**
     * @var MongoBundle\Document\Dependencies
     */
    protected $task_depended;

    /**
     * @var MongoBundle\Document\Task
     */
    protected $tasks_container;

    /**
     * @var MongoBundle\Document\Task
     */
    protected $container;

    /**
     * @var MongoBundle\Document\Project
     */
    protected $projects;

    /**
     * @var MongoBundle\Document\User
     */
    protected $creator_user;

    /**
     * @var MongoBundle\Document\Tag
     */
    protected $tags = array();

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
     * Set dueDate
     *
     * @param date $dueDate
     * @return self
     */
    public function setDueDate($dueDate)
    {
        $this->dueDate = $dueDate;
        return $this;
    }

    /**
     * Get dueDate
     *
     * @return date $dueDate
     */
    public function getDueDate()
    {
        return $this->dueDate;
    }

    /**
     * Set startedAt
     *
     * @param date $startedAt
     * @return self
     */
    public function setStartedAt($startedAt)
    {
        $this->startedAt = $startedAt;
        return $this;
    }

    /**
     * Get startedAt
     *
     * @return date $startedAt
     */
    public function getStartedAt()
    {
        return $this->startedAt;
    }

    /**
     * Set finishedAt
     *
     * @param date $finishedAt
     * @return self
     */
    public function setFinishedAt($finishedAt)
    {
        $this->finishedAt = $finishedAt;
        return $this;
    }

    /**
     * Get finishedAt
     *
     * @return date $finishedAt
     */
    public function getFinishedAt()
    {
        return $this->finishedAt;
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
     * Set isMilestone
     *
     * @param boolean $isMilestone
     * @return self
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
     * @return self
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
     * @param int $advance
     * @return self
     */
    public function setAdvance($advance)
    {
        $this->advance = $advance;

        return $this;
    }

    /**
     * Get advance
     *
     * @return int
     */
    public function getAdvance()
    {
        return $this->advance;
    }

    /**
     * Add ressources
     *
     * @param \MongoBundle\Entity\Ressources $ressources
     */
    public function addRessource(\MongoBundle\Entity\Ressources $ressources)
    {
        $this->ressources[] = $ressources;
    }

    /**
     * Remove ressources
     *
     * @param \MongoBundle\Entity\Ressources $ressources
     */
    public function removeRessource(\MongoBundle\Entity\Ressources $ressources)
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
     * @param MongoBundle\Document\Dependencies $dependence
     */
    public function addDependence(\MongoBundle\Document\Dependencies $dependence)
    {
        $this->dependence[] = $dependence;
    }

    /**
     * Remove dependence
     *
     * @param \MongoBundle\Document\Dependencies $dependence
     */
    public function removeDependence(\MongoBundle\Document\Dependencies $dependence)
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
     * @param \MongoBundle\Document\Dependencies $taskDepended
     */
    public function addTaskDepended(\MongoBundle\Document\Dependencies $taskDepended)
    {
        $this->task_depended[] = $taskDepended;
    }

    /**
     * Remove task_depended
     *
     * @param \MongoBundle\Document\Dependencies $taskDepended
     */
    public function removeTaskDepended(\MongoBundle\Document\Dependencies $taskDepended)
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
     * @param \MongoBundle\Document\Task $tasksContainer
     */
    public function addTasksContainer(\MongoBundle\Document\Task $tasksContainer)
    {
        $this->tasks_container[] = $tasksContainer;
    }

    /**
     * Remove tasks_container
     *
     * @param \MongoBundle\Document\Task $tasksContainer
     */
    public function removeTasksContainer(\MongoBundle\Document\Task $tasksContainer)
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
     * Set container
     *
     * @param \MongoBundle\Document\Task $container
     * @return self
     */
    public function setContainer(\MongoBundle\Document\Task $container = null)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Get container
     *
     * @return \MongoBundle\Document\Task
     */
    public function getContainer()
    {
        return $this->container;
    }
}
