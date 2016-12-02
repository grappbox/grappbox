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
     * @var string
     */
    protected $color;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->tasks = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function objectToArray()
    {
      return array(
        "id" => $this->id,
        "name" => $this->name,
        "color" => $this->color
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
     * Add task
     *
     * @param MongoBundle\Document\Task $task
     */
    public function addTask( $task)
    {
        $this->tasks[] = $task;
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
     * Set project
     *
     * @param MongoBundle\Document\Project $project
     * @return self
     */
    public function setProject( $project)
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

}
