<?php

namespace GrappboxBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Contains
 */
class Contains
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
     * @var \DateTime
     */
    private $beginDate;

    /**
     * @var \DateTime
     */
    private $endDate;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $tasks;

    /**
     * @var integer
     */
    private $projectID;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->tasks = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Contains
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
     * Set beginDate
     *
     * @param \DateTime $beginDate
     * @return Contains
     */
    public function setBeginDate($beginDate)
    {
        $this->beginDate = $beginDate;

        return $this;
    }

    /**
     * Get beginDate
     *
     * @return \DateTime 
     */
    public function getBeginDate()
    {
        return $this->beginDate;
    }

    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     * @return Contains
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate
     *
     * @return \DateTime 
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Add tasks
     *
     * @param \GrappboxBundle\Entity\Task $tasks
     * @return Contains
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
     * Set projectID
     *
     * @param integer $projectID
     * @return Contains
     */
    public function setProjectID($projectID)
    {
        $this->projectID = $projectID;

        return $this;
    }

    /**
     * Get projectID
     *
     * @return integer 
     */
    public function getProjectID()
    {
        return $this->projectID;
    }
}
