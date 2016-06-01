<?php

namespace GrappboxBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * StatTasksRepartition
 */
class StatTasksRepartition
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
     * @var string
     */
    private $role;

    /**
     * @var integer
     */
    private $value;

    /**
     * @var float
     */
    private $percentage;

    /**
     * @var \GrappboxBundle\Entity\Project
     */
    private $project;

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
     * @return StatTasksRepartition
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
     * @return StatTasksRepartition
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
     * @param integer $value
     * @return StatTasksRepartition
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return integer
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set percentage
     *
     * @param float $percentage
     * @return StatTasksRepartition
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
