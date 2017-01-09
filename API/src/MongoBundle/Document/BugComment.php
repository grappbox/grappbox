<?php

namespace MongoBundle\Document;


/**
 * BugComment
 */
class BugComment
{
    /**
     * @var $id
     */
    protected $id;

    /**
     * @var string $comment
     */
    protected $comment;

    /**
     * @var date $createdAt
     */
    protected $createdAt;

    /**
     * @var date $editedAt
     */
    protected $editedAt;

    /**
     * @var date $deletedAt
     */
    protected $deletedAt;

    /**
     * @var MongoBundle\Document\Bug $bugs
     */
    protected $bugs;

    /**
     * @var MongoBundle\Document\User $creator
     */
    protected $creator;

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
        "parentId" => $this->bugs->getId(),
        "comment" => $this->comment,
        "createdAt" => $this->createdAt ? $this->createdAt->format('Y-m-d H:i:s') : null,
        "editedAt" => $this->editedAt ? $this->editedAt->format('Y-m-d H:i:s') : null,
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
     * Set bugs
     *
     * @param MongoBundle\Document\Bug $bug
     * @return self
     */
    public function setBugs($bug)
    {
        $this->bugs = $bug;
        return $this;
    }

    /**
     * Get bugs
     *
     * @return MongoBundle\Document\Bug
     */
    public function getBugs()
    {
        return $this->bugs;
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
