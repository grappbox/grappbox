<?php

namespace MongoBundle\Document;



/**
 * MongoBundle\Document\Timeline
 */
class Timeline
{
    /**
     * @var $id
     */
    protected $id;

    /**
     * @var int $typeId
     */
    protected $typeId;

    /**
     * @var int $projectId
     */
    protected $projectId;

    /**
     * @var string $name
     */
    protected $name;

    /**
     * @var MongoBundle\Document\Project
     */
    protected $projects;

    /**
     * @var MongoBundle\Document\TimelineMessage
     */
    protected $timelineMessages = array();

    public function __construct()
    {
        $this->timelineMessages = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set typeId
     *
     * @param int $typeId
     * @return self
     */
    public function setTypeId($typeId)
    {
        $this->typeId = $typeId;
        return $this;
    }

    /**
     * Get typeId
     *
     * @return int $typeId
     */
    public function getTypeId()
    {
        return $this->typeId;
    }

    /**
     * Set projectId
     *
     * @param int $projectId
     * @return self
     */
    public function setProjectId($projectId)
    {
        $this->projectId = $projectId;
        return $this;
    }

    /**
     * Get projectId
     *
     * @return int $projectId
     */
    public function getProjectId()
    {
        return $this->projectId;
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

    /**
     * Add timelineMessage
     *
     * @param MongoBundle\Document\TimelineMessage $timelineMessage
     */
    public function addTimelineMessage(\MongoBundle\Document\TimelineMessage $timelineMessage)
    {
        $this->timelineMessages[] = $timelineMessage;
    }

    /**
     * Remove timelineMessage
     *
     * @param MongoBundle\Document\TimelineMessage $timelineMessage
     */
    public function removeTimelineMessage(\MongoBundle\Document\TimelineMessage $timelineMessage)
    {
        $this->timelineMessages->removeElement($timelineMessage);
    }

    /**
     * Get timelineMessages
     *
     * @return \Doctrine\Common\Collections\Collection $timelineMessages
     */
    public function getTimelineMessages()
    {
        return $this->timelineMessages;
    }
}