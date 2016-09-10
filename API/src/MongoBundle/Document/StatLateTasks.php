<?php

namespace MongoBundle\Document;


/**
 * MongoBundle\Document\StatLateTasks
 */
class StatLateTasks
{
    /**
     * @var id
     */
    private $id;

    /**
     * @var date
     */
    private $date;

    /**
     * @var string
     */
    private $user;

    /**
     * @var string
     */
    private $role;

    /**
     * @var int
     */
    private $lateTasks;

    /**
     * @var int
     */
    private $ontimeTasks;

    /**
     * @var MongoBundle\Document\Project
     */
    private $project;

    public function objectToArray()
    {
      return array(
        "user" => $this->user,
        "role" => $this->role,
        "date" => $this->date,
        "lateTasks" => $this->lateTasks,
        "ontimeTasks" => $this->ontimeTasks,
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
     * Set date
     *
     * @param date $date
     * @return self
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return date
     */
    public function getDate()
    {
        return $this->date;
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
     * Set lateTasks
     *
     * @param int $lateTasks
     * @return self
     */
    public function setLateTasks($lateTasks)
    {
        $this->lateTasks = $lateTasks;

        return $this;
    }

    /**
     * Get lateTasks
     *
     * @return int
     */
    public function getLateTasks()
    {
        return $this->lateTasks;
    }

    /**
     * Set ontimeTasks
     *
     * @param int $ontimeTasks
     * @return self
     */
    public function setOntimeTasks($ontimeTasks)
    {
        $this->ontimeTasks = $ontimeTasks;

        return $this;
    }

    /**
     * Get ontimeTasks
     *
     * @return int
     */
    public function getOntimeTasks()
    {
        return $this->ontimeTasks;
    }

    /**
     * Set project
     *
     * @param \MongoBundle\Document\Project $project
     * @return Project
     */
    public function setProject(\MongoBundle\Document\Project $project = null)
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
