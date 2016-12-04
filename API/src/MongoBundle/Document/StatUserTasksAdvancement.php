<?php

namespace MongoBundle\Document;



/**
 * MongoBundle\Document\StatUserTasksAdvancement
 */
class StatUserTasksAdvancement
{
    /**
     * @var id
     */
    protected $id;

    /**
     * @var int
     */
    protected $tasksToDo;

    /**
     * @var int
     */
    protected $tasksDoing;

    /**
     * @var int
     */
    protected $tasksDone;

    /**
     * @var int
     */
    protected $tasksLate;

    /**
     * @var MongoBundle\Document\Project
     */
    protected $project;

    /**
     * @var MongoBundle\Document\User
     */
    protected $user;

    public function objectToArray()
    {
        return array(
          "user" => array("id" => $this->getUser()->getId(),
                          "firstname" => $this->getUser()->getFirstname(),
                          "lastname" => $this->getUser()->getLastname()),
          "tasksToDo" => $this->tasksToDo,
          "tasksDoing" => $this->tasksDoing,
          "tasksDone" => $this->tasksDone,
          "tasksLate" => $this->tasksLate
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
     * Set tasksToDo
     *
     * @param int $tasksToDo
     * @return self
     */
    public function setTasksToDo($tasksToDo)
    {
        $this->tasksToDo = $tasksToDo;

        return $this;
    }

    /**
     * Get tasksToDo
     *
     * @return int
     */
    public function getTasksToDo()
    {
        return $this->tasksToDo;
    }

    /**
     * Set tasksDoing
     *
     * @param int $tasksDoing
     * @return self
     */
    public function setTasksDoing($tasksDoing)
    {
        $this->tasksDoing = $tasksDoing;

        return $this;
    }

    /**
     * Get tasksDoing
     *
     * @return int
     */
    public function getTasksDoing()
    {
        return $this->tasksDoing;
    }

    /**
     * Set tasksDone
     *
     * @param int $tasksDone
     * @return self
     */
    public function setTasksDone($tasksDone)
    {
        $this->tasksDone = $tasksDone;

        return $this;
    }

    /**
     * Get tasksDone
     *
     * @return int
     */
    public function getTasksDone()
    {
        return $this->tasksDone;
    }

    /**
     * Set tasksLate
     *
     * @param int $tasksLate
     * @return self
     */
    public function setTasksLate($tasksLate)
    {
        $this->tasksLate = $tasksLate;

        return $this;
    }

    /**
     * Get tasksLate
     *
     * @return int
     */
    public function getTasksLate()
    {
        return $this->tasksLate;
    }

    /**
     * Set project
     *
     * @param MongoBundle\Document\Project $project
     * @return self
     */
    public function setProject( $project)
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

    /**
     * Set user
     *
     * @param MongoBundle\Document\User $user
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
     * @return MongoBundle\Document\User
     */
    public function getUser()
    {
        return $this->user;
    }
}
