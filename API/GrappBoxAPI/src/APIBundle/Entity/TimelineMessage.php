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
    private $title;

    /**
     * @var string
     */
    private $message;

    /**
     * @var integer
     */
    private $parentId;


    /**
     * @var \APIBundle\Entity\Timeline
     */
    private $timelines;

    /**
     * @var DateTime
     */
    private $createdAt;

    /**
     * @var DateTime
     */
    private $editedAt;

    /**
     * @var DateTime
     */
    private $deletedAt;


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
     * Set title
     *
     * @param string $title
     * @return TimelineMessage
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
     * Set parentId
     *
     * @param integer $parenteId
     * @return TimelineMessage
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;

        return $this;
    }

    /**
     * Get parentId
     *
     * @return integer
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * Set createdAt
     *
     * @param DateTime $createdAt
     * @return TimelineMessage
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set editedAt
     *
     * @param DateTime $editedAt
     * @return TimelineMessage
     */
    public function setEditedAt($editedAt)
    {
        $this->editedAt = $editedAt;

        return $this;
    }

    /**
     * Get editedAt
     *
     * @return DateTime
     */
    public function getEditedAt()
    {
        return $this->editedAt;
    }

    /**
     * Set deletedAt
     *
     * @param DateTime $deletedAt
     * @return TimelineMessage
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Get deletedAt
     *
     * @return DateTime
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
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

    /**
     * Get object content into array
     *
     * @return array
     */
    public function objectToArray()
    {
      return array(
        "id" => $this->id,
        "userId" => $this->userId,
        "timelineId" => $this->timelineId,
        "title" => $this->title,
        "message" => $this->message,
        "parentId" => $this->parentId,
        "createdAt" => $this->createdAt,
        "editedAt" => $this->editedAt,
        "deletedAt" => $this->deletedAt
      );
    }

}
