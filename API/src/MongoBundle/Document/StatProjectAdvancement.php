<?php

namespace MongoBundle\Document;



/**
 * MongoBundle\Document\StatProjectAdvancement
 */
class StatProjectAdvancement
{
    /**
     * @var id
     */
    protected $id;

    /**
     * @var int
     */
    protected $percentage;

    /**
     * @var int
     */
    protected $progress;

    /**
     * @var int
     */
    protected $totalTasks;

    /**
     * @var int
     */
    protected $finishedTasks;

    /**
     * @var date
     */
    protected $date;

    /**
     * @var MongoBundle\Document\Project
     */
    protected $project;

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
     * @return id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set percentage
     *
     * @param int $percentage
     * @return self
     */
    public function setPercentage($percentage)
    {
        $this->percentage = $percentage;

        return $this;
    }

    /**
     * Get percentage
     *
     * @return int
     */
    public function getPercentage()
    {
        return $this->percentage;
    }

    /**
     * Set progress
     *
     * @param int $progress
     * @return self
     */
    public function setProgress($progress)
    {
        $this->progress = $progress;

        return $this;
    }

    /**
     * Get progress
     *
     * @return int
     */
    public function getProgress()
    {
        return $this->progress;
    }

    /**
     * Set totalTasks
     *
     * @param int $totalTasks
     * @return self
     */
    public function setTotalTasks($totalTasks)
    {
        $this->totalTasks = $totalTasks;

        return $this;
    }

    /**
     * Get totalTasks
     *
     * @return int
     */
    public function getTotalTasks()
    {
        return $this->totalTasks;
    }

    /**
     * Set finishedTasks
     *
     * @param int $finishedTasks
     * @return self
     */
    public function setFinishedTasks($finishedTasks)
    {
        $this->finishedTasks = $finishedTasks;

        return $this;
    }

    /**
     * Get finishedTasks
     *
     * @return int
     */
    public function getFinishedTasks()
    {
        return $this->finishedTasks;
    }

    /**
     * Set project
     *
     * @param MongoBundle\Document\Project $project
     * @return Project
     */
    public function setProject(\MongoBundle\Entity\Project $project = null)
    {
        $this->project = $project;

        return $this;
    }

    /**
     * Get project
     *
     * @return MongoBundle\Document\Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * Set date
     *
     * @param date $date
     * @return self
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return date
     */
    public function getDate()
    {
        return $this->date;
    }
}
