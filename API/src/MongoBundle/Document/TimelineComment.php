<?php

namespace MongoBundle\Document;


/**
 * TimelineComment
 */
class TimelineComment
{
    /**
     * @var $id
     */
    private $id;

    /**
     * @var string
     */
    private $comment;

    /**
     * @var date
     */
    private $createdAt;

    /**
     * @var date
     */
    private $editedAt;

    /**
     * @var date
     */
    private $deletedAt;

    /**
     * @var MongoBundle\Document\TimelineMessage
     */
    private $messages;

    /**
     * @var MongoBundle\Document\User
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
     * @return id $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set comment
     *
     * @param string $comment
     * @return self
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
     * @return date
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
     * @return date
     */
    public function getEditedAt()
    {
        return $this->editedAt;
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
     * @return date
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * Set messages
     *
     * @param MongoBundle\Document\Timeline $messages
     * @return self
     */
    public function setMessages($messages)
    {
        $this->messages = $messages;
        return $this;
    }

    /**
     * Get messages
     *
     * @return MongoBundle\Document\TimelineMessage
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Set creator
     *
     * @param MongoBundle\Document\User $creator
     * @return self
     */
    public function setCreator($creator)
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

}
