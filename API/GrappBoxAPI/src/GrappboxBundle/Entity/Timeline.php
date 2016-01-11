<?php

namespace APIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Timeline
 */
class Timeline
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $typeId;

    /**
     * @var integer
     */
    private $projectId;

    /**
     * @var string
     */
    private $name;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $timelineMessages;

    /**
     * @var \APIBundle\Entity\Project
     */
    private $projects;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->timelineMessages = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set typeId
     *
     * @param integer $typeId
     * @return Timeline
     */
    public function setTypeId($typeId)
    {
        $this->typeId = $typeId;

        return $this;
    }

    /**
     * Get typeId
     *
     * @return integer
     */
    public function getTypeId()
    {
        return $this->typeId;
    }

    /**
     * Set projectId
     *
     * @param integer $projectId
     * @return Timeline
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
     * @return Timeline
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
     * Add timelineMessages
     *
     * @param \APIBundle\Entity\TimelineMessage $timelineMessages
     * @return Timeline
     */
    public function addTimelineMessage(\APIBundle\Entity\TimelineMessage $timelineMessages)
    {
        $this->timelineMessages[] = $timelineMessages;

        return $this;
    }

    /**
     * Remove timelineMessages
     *
     * @param \APIBundle\Entity\TimelineMessage $timelineMessages
     */
    public function removeTimelineMessage(\APIBundle\Entity\TimelineMessage $timelineMessages)
    {
        $this->timelineMessages->removeElement($timelineMessages);
    }

    /**
     * Get timelineMessages
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTimelineMessages()
    {
        return $this->timelineMessages;
    }

    /**
     * Set projects
     *
     * @param \APIBundle\Entity\Project $projects
     * @return Timeline
     */
    public function setProjects(\APIBundle\Entity\Project $projects = null)
    {
        $this->projects = $projects;

        return $this;
    }

    /**
     * Get projects
     *
     * @return \APIBundle\Entity\Project
     */
    public function getProjects()
    {
        return $this->projects;
    }

    /**
     * Get object content into array
     *
     * @return array
     */
    public function objectToArray()
    {
      return array(
        "id" => $this->id,
        "typeId" => $this->typeId,
        "projectId" => $this->projectId,
        "name" => $this->name
      );
    }
}
