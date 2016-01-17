<?php

namespace MongoBundle\Document;

class Role
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
     * @var int $teamTimeline
     */
    protected $teamTimeline;

    /**
     * @var int $customerTimeline
     */
    protected $customerTimeline;

    /**
     * @var int $gantt
     */
    protected $gantt;

    /**
     * @var int $whiteboard
     */
    protected $whiteboard;

    /**
     * @var int $bugtracker
     */
    protected $bugtracker;

    /**
     * @var int $event
     */
    protected $event;

    /**
     * @var int $task
     */
    protected $task;

    /**
     * @var int $projectSettings
     */
    protected $projectSettings;

    /**
     * @var int $cloud
     */
    protected $cloud;

    /**
     * @var MongoBundle\Document\Project
     */
    protected $projects;


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
     * Set teamTimeline
     *
     * @param int $teamTimeline
     * @return self
     */
    public function setTeamTimeline($teamTimeline)
    {
        $this->teamTimeline = $teamTimeline;
        return $this;
    }

    /**
     * Get teamTimeline
     *
     * @return int $teamTimeline
     */
    public function getTeamTimeline()
    {
        return $this->teamTimeline;
    }

    /**
     * Set customerTimeline
     *
     * @param int $customerTimeline
     * @return self
     */
    public function setCustomerTimeline($customerTimeline)
    {
        $this->customerTimeline = $customerTimeline;
        return $this;
    }

    /**
     * Get customerTimeline
     *
     * @return int $customerTimeline
     */
    public function getCustomerTimeline()
    {
        return $this->customerTimeline;
    }

    /**
     * Set gantt
     *
     * @param int $gantt
     * @return self
     */
    public function setGantt($gantt)
    {
        $this->gantt = $gantt;
        return $this;
    }

    /**
     * Get gantt
     *
     * @return int $gantt
     */
    public function getGantt()
    {
        return $this->gantt;
    }

    /**
     * Set whiteboard
     *
     * @param int $whiteboard
     * @return self
     */
    public function setWhiteboard($whiteboard)
    {
        $this->whiteboard = $whiteboard;
        return $this;
    }

    /**
     * Get whiteboard
     *
     * @return int $whiteboard
     */
    public function getWhiteboard()
    {
        return $this->whiteboard;
    }

    /**
     * Set bugtracker
     *
     * @param int $bugtracker
     * @return self
     */
    public function setBugtracker($bugtracker)
    {
        $this->bugtracker = $bugtracker;
        return $this;
    }

    /**
     * Get bugtracker
     *
     * @return int $bugtracker
     */
    public function getBugtracker()
    {
        return $this->bugtracker;
    }

    /**
     * Set event
     *
     * @param int $event
     * @return self
     */
    public function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    /**
     * Get event
     *
     * @return int $event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set task
     *
     * @param int $task
     * @return self
     */
    public function setTask($task)
    {
        $this->task = $task;
        return $this;
    }

    /**
     * Get task
     *
     * @return int $task
     */
    public function getTask()
    {
        return $this->task;
    }

    /**
     * Set projectSettings
     *
     * @param int $projectSettings
     * @return self
     */
    public function setProjectSettings($projectSettings)
    {
        $this->projectSettings = $projectSettings;
        return $this;
    }

    /**
     * Get projectSettings
     *
     * @return int $projectSettings
     */
    public function getProjectSettings()
    {
        return $this->projectSettings;
    }

    /**
     * Set cloud
     *
     * @param int $cloud
     * @return self
     */
    public function setCloud($cloud)
    {
        $this->cloud = $cloud;
        return $this;
    }

    /**
     * Get cloud
     *
     * @return int $cloud
     */
    public function getCloud()
    {
        return $this->cloud;
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
}
