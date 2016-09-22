<?php

namespace SQLBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TimelineComment
 */
class TimelineComment
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $comment;

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
     * @var \SQLBundle\Entity\TimelineMessage
     */
    private $messages;

    /**
     * @var \SQLBundle\Entity\User
     */
    private $creator;

    /**
     * Get object content into array
     *
     * @return array
     */
    public function objectToArray()
    {
        return array(
          "id" => $this->id,
          "creator" => array("id" => $this->creator->getID(), "firstname" => $this->creator->getFirstname(), "lastname" => $this->creator->getLastName()),
          "parentId" => $this->messages->getId(),
          "comment" => $this->comment,
          "createdAt" => $this->createdAt ? $this->createdAt->format('Y-m-d H:i:s') : null,
          "editedAt" => $this->editedAt ? $this->editedAt->format('Y-m-d H:i:s') : null
        );
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
     * Set comment
     *
     * @param string $comment
     * @return TimelineMessage
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
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
     * Set messages
     *
     * @param \SQLBundle\Entity\Timeline $messages
     * @return TimelineMessage
     */
    public function setMessages(\SQLBundle\Entity\TimelineMessage $messages = null)
    {
        $this->messages = $messages;

        return $this;
    }

    /**
     * Get messages
     *
     * @return \SQLBundle\Entity\TimelineMessage
     */
    public function getMessages()
    {
        return $this->messages;
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

}
