<?php

namespace SQLBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * StatUserTasksAdvancement
 */
class StatUserTasksAdvancement
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $tasksToDo;

    /**
     * @var integer
     */
    private $tasksDoing;

    /**
     * @var integer
     */
    private $tasksDone;

    /**
     * @var integer
     */
    private $tasksLate;

    /**
     * @var \SQLBundle\Entity\Project
     */
    private $project;

    /**
     * @var \SQLBundle\Entity\User
     */
    private $user;

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
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set tasksToDo
     *
     * @param integer $tasksToDo
     * @return StatUserTasksAdvancement
     */
    public function setTasksToDo($tasksToDo)
    {
        $this->tasksToDo = $tasksToDo;

        return $this;
    }

    /**
     * Get tasksToDo
     *
     * @return integer
     */
    public function getTasksToDo()
    {
        return $this->tasksToDo;
    }

    /**
     * Set tasksDoing
     *
     * @param integer $tasksDoing
     * @return StatUserTasksAdvancement
     */
    public function setTasksDoing($tasksDoing)
    {
        $this->tasksDoing = $tasksDoing;

        return $this;
    }

    /**
     * Get tasksDoing
     *
     * @return integer
     */
    public function getTasksDoing()
    {
        return $this->tasksDoing;
    }

    /**
     * Set tasksDone
     *
     * @param integer $tasksDone
     * @return StatUserTasksAdvancement
     */
    public function setTasksDone($tasksDone)
    {
        $this->tasksDone = $tasksDone;

        return $this;
    }

    /**
     * Get tasksDone
     *
     * @return integer
     */
    public function getTasksDone()
    {
        return $this->tasksDone;
    }

    /**
     * Set tasksLate
     *
     * @param integer $tasksLate
     * @return StatUserTasksAdvancement
     */
    public function setTasksLate($tasksLate)
    {
        $this->tasksLate = $tasksLate;

        return $this;
    }

    /**
     * Get tasksLate
     *
     * @return integer
     */
    public function getTasksLate()
    {
        return $this->tasksLate;
    }

    /**
     * Set project
     *
     * @param \SQLBundle\Entity\Project $project
     * @return Project
     */
    public function setProject(\SQLBundle\Entity\Project $project)
    {
        $this->project = $project;

        return $this;
    }

    /**
     * Get project
     *
     * @return \SQLBundle\Entity\Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * Set user
     *
     * @param \SQLBundle\Entity\User $user
     * @return User
     */
    public function setUser(\SQLBundle\Entity\User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \SQLBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }
}
