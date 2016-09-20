<?php

namespace SQLBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Task
 */
class Task
{
    public function objectToArray($taskModified)
    {
        $tasks = array();
        print(count($this->tasks_container));
        foreach ($this->tasks_container as $t) {
            $start = $t->getStartedAt();
            if ($start != null)
                $start = $start->format('Y-m-d H:i:s');
            $due = $t->getDueDate();
            if ($due != null)
                $due = $due->format('Y-m-d H:i:s');
            $tasks[] = array("id" => $t->getId(), "title" => $t->getTitle(), "started_at" => $start, "due_date" => $due);
        }
        $users = array();
        foreach ($this->ressources as $res) {
            $u = $res->getUser();
            $users[] = array("id" => $u->getId(), "firstname" => $u->getFirstname(), "lastname" => $u->getLastname(), "percent" => $res->getResource());
        }
        $tags = array();
        foreach ($this->tags as $t) {
            $tags[] = array("id" => $t->getId(), "name" => $t->getName());
        }
        $deps = array();
        foreach ($this->dependence as $d) {
            $t = $d->getDependenceTask();
            $start = $t->getStartedAt();
            if ($start != null)
                $start = $start->format('Y-m-d H:i:s');
            $due = $t->getDueDate();
            if ($due != null)
                $due = $due->format('Y-m-d H:i:s');

            $deps[] = array("id" => $d->getId(), "name" => $d->getName(), "task" => array("id" => $t->getId(), "title" => $t->getTitle(), "started_at" => $start, "due_date" => $due));
        }
        $due = null;
        $create = null;
        $start = null;
        $finish = null;
        if ($this->dueDate != null)
            $due = $this->dueDate->format('Y-m-d H:i:s');
        if ($this->createdAt != null)
            $create = $this->createdAt->format('Y-m-d H:i:s');
        if ($this->startedAt != null)
            $start = $this->startedAt->format('Y-m-d H:i:s');
        if ($this->finishedAt != null)
            $finish = $this->finishedAt->format('Y-m-d H:i:s');
        return array(
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'project_id' => $this->projects->getId(),
            'due_date' => $due,
            'started_at' => $start,
            'finished_at' => $finish,
            'created_at' => $create,
            'is_milestone' => $this->isMilestone,
            'is_container' => $this->isContainer,
            'tasks' => $tasks,
            'advance' => $this->advance,
            'creator' => array("id" => $this->creator_user->getId(), "firstname" => $this->creator_user->getFirstname(), "lastname" => $this->creator_user->getLastname()),
            'users' => $users,
            'tags' => $tags,
            'dependencies' => $deps,
            'tasks_modified' => $taskModified
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
     * @var \SQLBundle\Entity\Project
     */
    private $projects;

    /**
     * @var \SQLBundle\Entity\User
     */
    private $creator_user;

    /**
     * @var \SQLBundle\Entity\Task
     */
    private $container;

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
     * @param \SQLBundle\Entity\Ressources $ressources
     * @return Task
     */
    public function addRessource(\SQLBundle\Entity\Ressources $ressources)
    {
        $this->ressources[] = $ressources;

        return $this;
    }

    /**
     * Remove ressources
     *
     * @param \SQLBundle\Entity\Ressources $ressources
     */
    public function removeRessource(\SQLBundle\Entity\Ressources $ressources)
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
     * @param \SQLBundle\Entity\Dependencies $dependence
     * @return Task
     */
    public function addDependence(\SQLBundle\Entity\Dependencies $dependence)
    {
        $this->dependence[] = $dependence;

        return $this;
    }

    /**
     * Remove dependence
     *
     * @param \SQLBundle\Entity\Dependencies $dependence
     */
    public function removeDependence(\SQLBundle\Entity\Dependencies $dependence)
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
     * @param \SQLBundle\Entity\Dependencies $taskDepended
     * @return Task
     */
    public function addTaskDepended(\SQLBundle\Entity\Dependencies $taskDepended)
    {
        $this->task_depended[] = $taskDepended;

        return $this;
    }

    /**
     * Remove task_depended
     *
     * @param \SQLBundle\Entity\Dependencies $taskDepended
     */
    public function removeTaskDepended(\SQLBundle\Entity\Dependencies $taskDepended)
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
     * @param \SQLBundle\Entity\Task $tasksContainer
     * @return Task
     */
    public function addTasksContainer(\SQLBundle\Entity\Task $tasksContainer)
    {
        $this->tasks_container[] = $tasksContainer;

        return $this;
    }

    /**
     * Remove tasks_container
     *
     * @param \SQLBundle\Entity\Task $tasksContainer
     */
    public function removeTasksContainer(\SQLBundle\Entity\Task $tasksContainer)
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
     * @param \SQLBundle\Entity\Project $projects
     * @return Task
     */
    public function setProjects(\SQLBundle\Entity\Project $projects = null)
    {
        $this->projects = $projects;

        return $this;
    }

    /**
     * Get projects
     *
     * @return \SQLBundle\Entity\Project 
     */
    public function getProjects()
    {
        return $this->projects;
    }

    /**
     * Set creator_user
     *
     * @param \SQLBundle\Entity\User $creatorUser
     * @return Task
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
     * Set container
     *
     * @param \SQLBundle\Entity\Task $container
     * @return Task
     */
    public function setContainer(\SQLBundle\Entity\Task $container = null)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Get container
     *
     * @return \SQLBundle\Entity\Task 
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Add tags
     *
     * @param \SQLBundle\Entity\Tag $tags
     * @return Task
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
}
