<?php

namespace MongoBundle\Document;



/**
 * MongoBundle\Document\StatTasksRepartition
 */
class StatTasksRepartition
{
    /**
     * @var id
     */
    protected $id;

    /**
     * @var string
     */
    protected $user;

    /**
     * @var string
     */
    protected $role;

    /**
     * @var int
     */
    protected $value;

    /**
     * @var float
     */
    protected $percentage;

    /**
     * @var MongoBundle\Document\Project
     */
    protected $project;

    public function objectToArray()
    {
      return array(
        "user" => $this->user,
        //"role" => $this->role,
        "value" => $this->value,
        "percentage" => $this->percentage
      );
    }

    /**
     * Get id
     *
     * @return id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set user
     *
     * @param string $user
     * @return self
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set role
     *
     * @param string $role
     * @return self
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set value
     *
     * @param int $value
     * @return self
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set percentage
     *
     * @param float $percentage
     * @return self
     */
    public function setPercentage($percentage)
    {
        $this->percentage = $percentage;

        return $this;
    }

    /**
     * Get percentage
     *
     * @return float
     */
    public function getPercentage()
    {
        return $this->percentage;
    }

    /**
     * Set project
     *
     * @param MongoBundle\Document\Project $project
     * @return Project
     */
    public function setProject( $project = null)
    {
        $this->project = $project;

        return $this;
    }

    /**
     * Get project
     *
     * @return MongoBundle\Document\Project
     */
    public function getProject()
    {
        return $this->project;
    }
}
