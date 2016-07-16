<?php

namespace MongoBundle\Document;



/**
 * MongoBundle\Document\ProjectUserRole
 */
class ProjectUserRole
{
    /**
     * @var $id
     */
    protected $id;

    /**
     * @var int $projectId
     */
    protected $projectId;

    /**
     * @var int $userId
     */
    protected $userId;

    /**
     * @var int $roleId
     */
    protected $roleId;


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
     * Set projectId
     *
     * @param int $projectId
     * @return self
     */
    public function setProjectId($projectId)
    {
        $this->projectId = $projectId;
        return $this;
    }

    /**
     * Get projectId
     *
     * @return int $projectId
     */
    public function getProjectId()
    {
        return $this->projectId;
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
     * Set roleId
     *
     * @param int $roleId
     * @return self
     */
    public function setRoleId($roleId)
    {
        $this->roleId = $roleId;
        return $this;
    }

    /**
     * Get roleId
     *
     * @return int $roleId
     */
    public function getRoleId()
    {
        return $this->roleId;
    }
}
