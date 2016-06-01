<?php

namespace GrappboxBundle\Entity;

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
     * @var string
     */
    private $user;

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
     * @var \GrappboxBundle\Entity\Project
     */
    private $project;

    public function objectToArray()
    {
        return array(
          "user" => $this->user,
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
     * Set user
     *
     * @param string $user
     * @return StatUserTasksAdvancement
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
     * @param \GrappboxBundle\Entity\Project $project
     * @return Project
     */
    public function setProject(\GrappboxBundle\Entity\Project $project = null)
    {
        $this->project = $project;

        return $this;
    }

    /**
     * Get project
     *
     * @return \GrappboxBundle\Entity\Project
     */
    public function getProject()
    {
        return $this->project;
    }
}
