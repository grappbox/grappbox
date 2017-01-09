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
     * @var string $projectId
     */
    protected $projectId;

    /**
     * @var string $userId
     */
    protected $userId;

    /**
     * @var string $roleId
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
     * @param string $projectId
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
     * @return string $projectId
     */
    public function getProjectId()
    {
        return $this->projectId;
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
     * Set roleId
     *
     * @param string $roleId
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
     * @return string $roleId
     */
    public function getRoleId()
    {
        return $this->roleId;
    }
}
