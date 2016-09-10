<?php

namespace MongoBundle\Document;



/**
 * MongoBundle\Document\Gantt
 */
class Gantt
{
    /**
     * @var $id
     */
    protected $id;

    /**
     * @var date $updatedAt
     */
    protected $updatedAt;

    /**
     * @var date $createdAt
     */
    protected $createdAt;

    /**
     * @var MongoBundle\Document\Project
     */
    protected $projects;

    /**
     * @var MongoBundle\Document\User
     */
    protected $creator_user;

    /**
     * @var MongoBundle\Document\User
     */
    protected $updator_user;



    public function objectToArray()
    {
        return array(
            'id' => $this->id,
            'projectId' => $this->projects,
            'creatorId' => $this->creator_user,
            'updatorId' => $this->updator_user,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt
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
     * Set creatorUser
     *
     * @param MongoBundle\Document\User $creatorUser
     * @return self
     */
    public function setCreatorUser( $creatorUser)
    {
        $this->creator_user = $creatorUser;
        return $this;
    }

    /**
     * Get creatorUser
     *
     * @return MongoBundle\Document\User $creatorUser
     */
    public function getCreatorUser()
    {
        return $this->creator_user;
    }

    /**
     * Set updatorUser
     *
     * @param MongoBundle\Document\User $updatorUser
     * @return self
     */
    public function setUpdatorUser( $updatorUser)
    {
        $this->updator_user = $updatorUser;
        return $this;
    }

    /**
     * Get updatorUser
     *
     * @return MongoBundle\Document\User $updatorUser
     */
    public function getUpdatorUser()
    {
        return $this->updator_user;
    }
}
