<?php

namespace GrappboxBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tag
 */
class Tag
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
     * @var \GrappboxBundle\Entity\Project
     */
    private $project;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $tasks;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $bugs;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->tasks = new \Doctrine\Common\Collections\ArrayCollection();
        $this->bugs = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Tag
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
     * Add tasks
     *
     * @param \GrappboxBundle\Entity\Task $tasks
     * @return Tag
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
     * Set project
     *
     * @param \GrappboxBundle\Entity\Project $project
     * @return Tag
     */
    public function setProject(\GrappboxBundle\Entity\Project $project = null)
    {
        $this->project = $project;

        return $this;
    }

    /**
     * Get project
     *
     * @return \GrappboxBundle\Entity\Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * Add bugs
     *
     * @param \GrappboxBundle\Entity\Bug $bugs
     * @return Tag
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

    public function objectToArray()
    {
      return array(
        "id" => $this->id,
        "name" => $this->name,
        "projectId" => $this->project->getId()
      );
    }
}
