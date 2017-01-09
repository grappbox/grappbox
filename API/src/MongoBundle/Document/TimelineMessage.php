<?php

namespace MongoBundle\Document;



/**
 * MongoBundle\Document\TimelineMessage
 */
class TimelineMessage
{
    /**
     * @var $id
     */
    protected $id;

    /**
     * @var string $userId
     */
    protected $userId;

    /**
     * @var string $timelineId
     */
    protected $timelineId;

    /**
     * @var string $title
     */
    protected $title;

    /**
     * @var string $message
     */
    protected $message;

    /**
     * @var MongoBundle\Document\Timeline
     */
    protected $timelines;

    /**
     * @var date $createdAt
     */
    protected $createdAt;

    /**
     * @var date $editedAt
     */
    protected $editedAt;

    /**
     * @var MongoBundle\Document\User
     */
    protected $creator;

    /**
     * @var MongoBundle\Document\TimelineComment
     */
    protected $comments;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->comments = new \Doctrine\Common\Collections\ArrayCollection();
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
         "creator"=> array("id" => $this->creator->getId(), "firstname" => $this->creator->getFirstname(), "lastname" => $this->creator->getLastname()),
         "timelineId" => $this->timelines->getId(),
         "title" => $this->title,
         "message" => $this->message,
         "createdAt" => $this->createdAt ? $this->createdAt->format('Y-m-d H:i:s') : null,
         "editedAt" => $this->editedAt ? $this->editedAt->format('Y-m-d H:i:s') : null
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
     * @param string $userId
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
     * @return string $userId
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set timelineId
     *
     * @param string $timelineId
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
     * @return string $timelineId
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
     * @param string $message
     * @return self
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Get message
     *
     * @return string $message
     */
    public function getMessage()
    {
        return $this->message;
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
    public function setTimelines( $timelines)
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

    /**
     * Set creator
     *
     * @param MongoBundle\Document\User $creator
     * @return self
     */
    public function setCreator( $creator)
    {
        $this->creator = $creator;
        return $this;
    }

    /**
     * Get creator
     *
     * @return MongoBundle\Document\User
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * Add comments
     *
     * @param MongoBundle\Document\TimelineComment $comment
     * @return self
     */
    public function addComment($comment)
    {
        $this->comments[] = $comment;
        return $this;
    }

    /**
     * Remove comments
     *
     * @param MongoBundle\Document\TimelineComment $comment
     */
    public function removeComment($comment)
    {
        $this->comments->removeElement($comment);
    }

    /**
     * Get comments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getComments()
    {
        return $this->comments;
    }
}
