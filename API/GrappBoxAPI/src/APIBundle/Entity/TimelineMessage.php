<?php

namespace APIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TimelineMessage
 */
class TimelineMessage
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $userId;

    /**
     * @var integer
     */
    private $timelineId;

    /**
     * @var string
     */
    private $message;

    /**
     * @var \APIBundle\Entity\Timeline
     */
    private $timelines;


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
     * Set userId
     *
     * @param integer $userId
     * @return TimelineMessage
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return integer 
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set timelineId
     *
     * @param integer $timelineId
     * @return TimelineMessage
     */
    public function setTimelineId($timelineId)
    {
        $this->timelineId = $timelineId;

        return $this;
    }

    /**
     * Get timelineId
     *
     * @return integer 
     */
    public function getTimelineId()
    {
        return $this->timelineId;
    }

    /**
     * Set message
     *
     * @param string $message
     * @return TimelineMessage
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string 
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set timelines
     *
     * @param \APIBundle\Entity\Timeline $timelines
     * @return TimelineMessage
     */
    public function setTimelines(\APIBundle\Entity\Timeline $timelines = null)
    {
        $this->timelines = $timelines;

        return $this;
    }

    /**
     * Get timelines
     *
     * @return \APIBundle\Entity\Timeline 
     */
    public function getTimelines()
    {
        return $this->timelines;
    }
}
