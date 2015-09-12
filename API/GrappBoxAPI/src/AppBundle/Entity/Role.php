<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * role
 *
 * @ORM\Table(name="roles")
 * @ORM\Entity
 */
class Role
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="project_id", type="integer")
     */
    private $projectId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var integer
     *
     * @ORM\Column(name="team_timeline", type="smallint")
     */
    private $teamTimeline;

    /**
     * @var integer
     *
     * @ORM\Column(name="customer_timeline", type="smallint")
     */
    private $customerTimeline;

    /**
     * @var integer
     *
     * @ORM\Column(name="gantt", type="smallint")
     */
    private $gantt;

    /**
     * @var integer
     *
     * @ORM\Column(name="whiteboard", type="smallint")
     */
    private $whiteboard;

    /**
     * @var integer
     *
     * @ORM\Column(name="bugtracker", type="smallint")
     */
    private $bugtracker;

    /**
     * @var integer
     *
     * @ORM\Column(name="event", type="smallint")
     */
    private $event;

    /**
     * @var integer
     *
     * @ORM\Column(name="task", type="smallint")
     */
    private $task;

    /**
     * @var integer
     *
     * @ORM\Column(name="project_settings", type="smallint")
     */
    private $projectSettings;


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
     * Set projectId
     *
     * @param integer $projectId
     * @return role
     */
    public function setProjectId($projectId)
    {
        $this->projectId = $projectId;

        return $this;
    }

    /**
     * Get projectId
     *
     * @return integer
     */
    public function getProjectId()
    {
        return $this->projectId;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return role
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
     * @return role
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
     * @return role
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
     * @return role
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
     * @return role
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
     * @return role
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
     * @return role
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
     * @return role
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
     * @return role
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
}
