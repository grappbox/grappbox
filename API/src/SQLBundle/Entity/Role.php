<?php

namespace SQLBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Role
 */
class Role
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
     * @var integer
     */
    private $teamTimeline;

    /**
     * @var integer
     */
    private $customerTimeline;

    /**
     * @var integer
     */
    private $gantt;

    /**
     * @var integer
     */
    private $whiteboard;

    /**
     * @var integer
     */
    private $bugtracker;

    /**
     * @var integer
     */
    private $event;

    /**
     * @var integer
     */
    private $task;

    /**
     * @var integer
     */
    private $projectSettings;

    /**
     * @var integer
     */
    private $cloud;

    /**
     * @var \SQLBundle\Entity\Project
     */
    private $projects;


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
     * @return Role
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
     * Set teamTimeline
     *
     * @param integer $teamTimeline
     * @return Role
     */
    public function setTeamTimeline($teamTimeline)
    {
        $this->teamTimeline = $teamTimeline;

        return $this;
    }

    /**
     * Get teamTimeline
     *
     * @return integer
     */
    public function getTeamTimeline()
    {
        return $this->teamTimeline;
    }

    /**
     * Set customerTimeline
     *
     * @param integer $customerTimeline
     * @return Role
     */
    public function setCustomerTimeline($customerTimeline)
    {
        $this->customerTimeline = $customerTimeline;

        return $this;
    }

    /**
     * Get customerTimeline
     *
     * @return integer
     */
    public function getCustomerTimeline()
    {
        return $this->customerTimeline;
    }

    /**
     * Set gantt
     *
     * @param integer $gantt
     * @return Role
     */
    public function setGantt($gantt)
    {
        $this->gantt = $gantt;

        return $this;
    }

    /**
     * Get gantt
     *
     * @return integer
     */
    public function getGantt()
    {
        return $this->gantt;
    }

    /**
     * Set whiteboard
     *
     * @param integer $whiteboard
     * @return Role
     */
    public function setWhiteboard($whiteboard)
    {
        $this->whiteboard = $whiteboard;

        return $this;
    }

    /**
     * Get whiteboard
     *
     * @return integer
     */
    public function getWhiteboard()
    {
        return $this->whiteboard;
    }

    /**
     * Set bugtracker
     *
     * @param integer $bugtracker
     * @return Role
     */
    public function setBugtracker($bugtracker)
    {
        $this->bugtracker = $bugtracker;

        return $this;
    }

    /**
     * Get bugtracker
     *
     * @return integer
     */
    public function getBugtracker()
    {
        return $this->bugtracker;
    }

    /**
     * Set event
     *
     * @param integer $event
     * @return Role
     */
    public function setEvent($event)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Get event
     *
     * @return integer
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set task
     *
     * @param integer $task
     * @return Role
     */
    public function setTask($task)
    {
        $this->task = $task;

        return $this;
    }

    /**
     * Get task
     *
     * @return integer
     */
    public function getTask()
    {
        return $this->task;
    }

    /**
     * Set projectSettings
     *
     * @param integer $projectSettings
     * @return Role
     */
    public function setProjectSettings($projectSettings)
    {
        $this->projectSettings = $projectSettings;

        return $this;
    }

    /**
     * Get projectSettings
     *
     * @return integer
     */
    public function getProjectSettings()
    {
        return $this->projectSettings;
    }

    /**
     * Set cloud
     *
     * @param integer $cloud
     * @return Role
     */
    public function setCloud($cloud)
    {
        $this->cloud = $cloud;

        return $this;
    }

    /**
     * Get cloud
     *
     * @return integer
     */
    public function getCloud()
    {
        return $this->cloud;
    }

    /**
     * Set projects
     *
     * @param \SQLBundle\Entity\Project $projects
     * @return Role
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

    public function getPart($part)
    {
      switch ($part) {
        case 'team_timeline':
          return $this->teamTimeline;
          break;
        case 'customer_timeline':
          return $this->customerTimeline;
          break;
        case 'gantt':
          return $this->gantt;
          break;
        case 'whiteboard':
          return $this->whiteboard;
          break;
        case 'bugtracker':
          return $this->bugtracker;
          break;
        case 'event':
          return $this->event;
          break;
        case 'task':
          return $this->task;
          break;
        case 'project_settings':
          return $this->projectSettings;
          break;
        case 'cloud':
          return $this->cloud;
          break;
        default:
          return -1;
          break;
      }
    }

    public function objectToArray()
    {
      return array(
        'projectId'=> $this->getProjects()->getId(),
        'roleId' => $this->getId(),
        'name'=> $this->getName(),
        'teamTimeline'=> $this->getTeamTimeline(),
      	'customerTimeline'=> $this->getCustomerTimeline(),
      	'gantt'=> $this->getGantt(),
      	'whiteboard'=> $this->getWhiteboard(),
      	'bugtracker'=> $this->getBugtracker(),
      	'event'=> $this->getEvent(),
      	'task'=> $this->getTask(),
      	'projectSettings'=> $this->getProjectSettings(),
      	'cloud'=> $this->getCloud()
      );
    }
}
