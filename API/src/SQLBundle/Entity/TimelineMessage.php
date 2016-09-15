<?php

namespace SQLBundle\Entity;

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
     * @var \SQLBundle\Entity\Timeline
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
     * @var \SQLBundle\Entity\User
     */
    private $creator;

    /**
     * @var \Doctrine\Common\Collections\User
     */
    private $comments;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->comments = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @param \SQLBundle\Entity\Timeline $timelines
     * @return TimelineMessage
     */
    public function setTimelines(\SQLBundle\Entity\Timeline $timelines = null)
    {
        $this->timelines = $timelines;

        return $this;
    }

    /**
     * Get timelines
     *
     * @return \SQLBundle\Entity\Timeline
     */
    public function getTimelines()
    {
        return $this->timelines;
    }

    /**
     * Set creator
     *
     * @param \SQLBundle\Entity\User $creator
     * @return Bug
     */
    public function setCreator(\SQLBundle\Entity\User $creator = null)
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * Get creator
     *
     * @return \SQLBundle\Entity\User
     */
    public function getCreator()
    {
        return $this->creator;
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
        "timelineId" => $this->timelineId,
        "title" => $this->title,
        "message" => $this->message,
        "createdAt" => $this->createdAt,
        "editedAt" => $this->editedAt,
        "deletedAt" => $this->deletedAt
      );
    }

    /**
     * Add comments
     *
     * @param \SQLBundle\Entity\TimelineComment $comment
     * @return Bug
     */
    public function addComment(\SQLBundle\Entity\TimelineComment $comment)
    {
        $this->comments[] = $comment;

        return $this;
    }

    /**
     * Remove comments
     *
     * @param \SQLBundle\Entity\TimelineComment $comment
     */
    public function removeComment(\SQLBundle\Entity\TimelineComment $comment)
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
