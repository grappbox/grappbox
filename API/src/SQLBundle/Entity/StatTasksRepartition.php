<?php

namespace SQLBundle\Entity;

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
     * @return StatTasksRepartition
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
