<?php

namespace GrappboxBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Dependencies
 */
class Dependencies
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
     * @var \GrappboxBundle\Entity\Task
     */
    private $dependence_task;

    /**
     * @var \GrappboxBundle\Entity\Task
     */
    private $task;


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
     * @return Dependencies
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
     * Set dependence_task
     *
     * @param \GrappboxBundle\Entity\Task $dependenceTask
     * @return Dependencies
     */
    public function setDependenceTask(\GrappboxBundle\Entity\Task $dependenceTask = null)
    {
        $this->dependence_task = $dependenceTask;

        return $this;
    }

    /**
     * Get dependence_task
     *
     * @return \GrappboxBundle\Entity\Task 
     */
    public function getDependenceTask()
    {
        return $this->dependence_task;
    }

    /**
     * Set task
     *
     * @param \GrappboxBundle\Entity\Task $task
     * @return Dependencies
     */
    public function setTask(\GrappboxBundle\Entity\Task $task = null)
    {
        $this->task = $task;

        return $this;
    }

    /**
     * Get task
     *
     * @return \GrappboxBundle\Entity\Task 
     */
    public function getTask()
    {
        return $this->task;
    }
}
