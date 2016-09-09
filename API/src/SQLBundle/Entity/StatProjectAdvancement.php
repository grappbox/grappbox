<?php

namespace SQLBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * StatProjectAdvancement
 */
class StatProjectAdvancement
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $percentage;

    /**
     * @var integer
     */
    private $progress;

    /**
     * @var integer
     */
    private $totalTasks;

    /**
     * @var integer
     */
    private $finishedTasks;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var \SQLBundle\Entity\Project
     */
    private $project;

    public function objectToArray()
    {
      return array(
        "date" => $this->date,
        "percentage" => $this->percentage,
        "progress" => $this->progress,
        "totalTasks" => $this->totalTasks,
        "finishedTasks" => $this->finishedTasks,
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
     * Set percentage
     *
     * @param integer $percentage
     * @return statProjectAdvancement
     */
    public function setPercentage($percentage)
    {
        $this->percentage = $percentage;

        return $this;
    }

    /**
     * Get percentage
     *
     * @return integer
     */
    public function getPercentage()
    {
        return $this->percentage;
    }

    /**
     * Set progress
     *
     * @param integer $progress
     * @return statProjectAdvancement
     */
    public function setProgress($progress)
    {
        $this->progress = $progress;

        return $this;
    }

    /**
     * Get progress
     *
     * @return integer
     */
    public function getProgress()
    {
        return $this->progress;
    }

    /**
     * Set totalTasks
     *
     * @param integer $totalTasks
     * @return statProjectAdvancement
     */
    public function setTotalTasks($totalTasks)
    {
        $this->totalTasks = $totalTasks;

        return $this;
    }

    /**
     * Get totalTasks
     *
     * @return integer
     */
    public function getTotalTasks()
    {
        return $this->totalTasks;
    }

    /**
     * Set finishedTasks
     *
     * @param integer $finishedTasks
     * @return statProjectAdvancement
     */
    public function setFinishedTasks($finishedTasks)
    {
        $this->finishedTasks = $finishedTasks;

        return $this;
    }

    /**
     * Get finishedTasks
     *
     * @return integer
     */
    public function getFinishedTasks()
    {
        return $this->finishedTasks;
    }

    /**
     * Set project
     *
     * @param \SQLBundle\Entity\Project $project
     * @return Project
     */
    public function setProject(\SQLBundle\Entity\Project $project = null)
    {
        $this->project = $project;

        return $this;
    }

    /**
     * Get project
     *
     * @return \SQLBundle\Entity\Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return statProjectAdvancement
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }
}
