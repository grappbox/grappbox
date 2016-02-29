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
    private $dependence;

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
     * Set dependence
     *
     * @param \GrappboxBundle\Entity\Task $dependence
     * @return Dependencies
     */
    public function setDependence(\GrappboxBundle\Entity\Task $dependence = null)
    {
        $this->dependence = $dependence;

        return $this;
    }

    /**
     * Get dependence
     *
     * @return \GrappboxBundle\Entity\Task 
     */
    public function getDependence()
    {
        return $this->dependence;
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
