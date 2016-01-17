<?php

namespace MongoBundle\Document;

class TimelineMessage
{

    /**
     * @var $id
     */
    protected $id;

    /**
     * @var int $userId
     */
    protected $userId;

    /**
     * @var int $timelineId
     */
    protected $timelineId;

    /**
     * @var string $title
     */
    protected $title;

    /**
     * @var text $message
     */
    protected $message;

    /**
     * @var int $parentId
     */
    protected $parentId;

    /**
     * @var date $createdAt
     */
    protected $createdAt;

    /**
     * @var date $deletedAt
     */
    protected $deletedAt;

    /**
     * @var date $editedAt
     */
    protected $editedAt;

    /**
     * @var MongoBundle\Document\Timeline
     */
    protected $timelines;

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
     * Set userId
     *
     * @param int $userId
     * @return self
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * Get userId
     *
     * @return int $userId
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set timelineId
     *
     * @param int $timelineId
     * @return self
     */
    public function setTimelineId($timelineId)
    {
        $this->timelineId = $timelineId;
        return $this;
    }

    /**
     * Get timelineId
     *
     * @return int $timelineId
     */
    public function getTimelineId()
    {
        return $this->timelineId;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return self
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Get title
     *
     * @return string $title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set message
     *
     * @param text $message
     * @return self
     */
    public function setMessage(\text $message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Get message
     *
     * @return text $message
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set parentId
     *
     * @param int $parentId
     * @return self
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;
        return $this;
    }

    /**
     * Get parentId
     *
     * @return int $parentId
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * Set createdAt
     *
     * @param date $createdAt
     * @return self
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * Get createdAt
     *
     * @return date $createdAt
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set deletedAt
     *
     * @param date $deletedAt
     * @return self
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;
        return $this;
    }

    /**
     * Get deletedAt
     *
     * @return date $deletedAt
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * Set editedAt
     *
     * @param date $editedAt
     * @return self
     */
    public function setEditedAt($editedAt)
    {
        $this->editedAt = $editedAt;
        return $this;
    }

    /**
     * Get editedAt
     *
     * @return date $editedAt
     */
    public function getEditedAt()
    {
        return $this->editedAt;
    }

    /**
     * Set timelines
     *
     * @param MongoBundle\Document\Timeline $timelines
     * @return self
     */
    public function setTimelines(\MongoBundle\Document\Timeline $timelines)
    {
        $this->timelines = $timelines;
        return $this;
    }

    /**
     * Get timelines
     *
     * @return MongoBundle\Document\Timeline $timelines
     */
    public function getTimelines()
    {
        return $this->timelines;
    }
}
