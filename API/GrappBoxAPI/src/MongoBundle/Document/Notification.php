<?php

namespace MongoBundle\Document;

class Notification
{

    /**
     * @var $id
     */
    protected $id;

    /**
     * @var string $type
     */
    protected $type;

    /**
     * @var int $targetId
     */
    protected $targetId;

    /**
     * @var text $message
     */
    protected $message;

    /**
     * @var date $createdAt
     */
    protected $createdAt;

    /**
     * @var boolean $isRead
     */
    protected $isRead;

    /**
     * @var MongoBundle\Document\User
     */
    protected $user;

    public function objectToArray()
    {
      // $projectId = null;
      // if ($this->projects)
      //   $projectId = $this->projects->getId();
        return array(
            'id' => $this->id,
            // TODO user ??
            "type" => $this->type,
            "targetId" => $this->targetId,
            "message" => $this->message,
            'createdAt' => $this->createdAt,
            'isRead' => $this->isRead
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
     * Set type
     *
     * @param string $type
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Get type
     *
     * @return string $type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set targetId
     *
     * @param int $targetId
     * @return self
     */
    public function setTargetId($targetId)
    {
        $this->targetId = $targetId;
        return $this;
    }

    /**
     * Get targetId
     *
     * @return int $targetId
     */
    public function getTargetId()
    {
        return $this->targetId;
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
     * Set isRead
     *
     * @param boolean $isRead
     * @return self
     */
    public function setIsRead($isRead)
    {
        $this->isRead = $isRead;
        return $this;
    }

    /**
     * Get isRead
     *
     * @return boolean $isRead
     */
    public function getIsRead()
    {
        return $this->isRead;
    }

    /**
     * Set user
     *
     * @param MongoBundle\Document\User $user
     * @return self
     */
    public function setUser(\MongoBundle\Document\User $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Get user
     *
     * @return MongoBundle\Document\User $user
     */
    public function getUser()
    {
        return $this->user;
    }
}
