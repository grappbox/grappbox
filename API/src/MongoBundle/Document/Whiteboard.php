<?php

namespace MongoBundle\Document;



/**
 * MongoBundle\Document\Whiteboard
 */
class Whiteboard
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
     * @var string $name
     */
    protected $name;

    /**
     * @var int $updatorId
     */
    protected $updatorId;

    /**
     * @var date $updatedAt
     */
    protected $updatedAt;

    /**
     * @var date $createdAt
     */
    protected $createdAt;

    /**
     * @var date $deletedAt
     */
    protected $deletedAt;

    /**
     * @var MongoBundle\Document\Project
     */
    protected $projects;

    /**
     * @var MongoBundle\Document\WhiteboardObject
     */
    protected $objects = array();

    public function __construct()
    {
        $this->objects = new \Doctrine\Common\Collections\ArrayCollection();
    }


    public function objectToArray()
    {
        return array(
            'id' => $this->id,
            'projectId' => $this->getProjects()->getId(),
            'userId' => $this->userId,
            'name' => $this->name,
            'updatorId' => $this->updatorId,
            'updatedAt' => $this->updatedAt,
            'createdAt' => $this->createdAt,
            'deletedAt' => $this->deletedAt
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
     * Set updatorId
     *
     * @param int $updatorId
     * @return self
     */
    public function setUpdatorId($updatorId)
    {
        $this->updatorId = $updatorId;
        return $this;
    }

    /**
     * Get updatorId
     *
     * @return int $updatorId
     */
    public function getUpdatorId()
    {
        return $this->updatorId;
    }

    /**
     * Set updatedAt
     *
     * @param date $updatedAt
     * @return self
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return date $updatedAt
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
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
     * Set projects
     *
     * @param MongoBundle\Document\Project $projects
     * @return self
     */
    public function setProjects( $projects)
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
     * Add object
     *
     * @param MongoBundle\Document\WhiteboardObject $object
     */
    public function addObject( $object)
    {
        $this->objects[] = $object;
    }

    /**
     * Remove object
     *
     * @param MongoBundle\Document\WhiteboardObject $object
     */
    public function removeObject( $object)
    {
        $this->objects->removeElement($object);
    }

    /**
     * Get objects
     *
     * @return \Doctrine\Common\Collections\Collection $objects
     */
    public function getObjects()
    {
        return $this->objects;
    }
}
