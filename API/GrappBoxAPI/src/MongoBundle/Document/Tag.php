<?php

namespace MongoBundle\Document;



/**
 * MongoBundle\Document\Tag
 */
class Tag
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
     * @var MongoBundle\Document\Project
     */
    protected $project;

    /**
     * @var MongoBundle\Document\Task
     */
    protected $tasks = array();

    /**
     * @var MongoBundle\Document\Bug
     */
    protected $bugs = array();

    public function __construct()
    {
        $this->tasks = new \Doctrine\Common\Collections\ArrayCollection();
        $this->bugs = new \Doctrine\Common\Collections\ArrayCollection();
    }



    public function objectToArray()
    {
      return array(
        "id" => $this->id,
        "name" => $this->name,
        "projectId" => $this->project->getId()
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
     * Set project
     *
     * @param MongoBundle\Document\Project $project
     * @return self
     */
    public function setProject(\MongoBundle\Document\Project $project)
    {
        $this->project = $project;
        return $this;
    }

    /**
     * Get project
     *
     * @return MongoBundle\Document\Project $project
     */
    public function getProject()
    {
        return $this->project;
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
}