<?php

namespace GrappboxBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * StatLateTasks
 */
class StatLateTasks
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
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
     * @var integer
     */
    private $lateTasks;

    /**
     * @var integer
     */
    private $ontimeTasks;

    /**
     * @var \GrappboxBundle\Entity\Project
     */
    private $project;

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
     * Set date
     *
     * @param \DateTime $date
     * @return StatLateTasks
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set user
     *
     * @param string $user
     * @return StatLateTasks
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
     * @return StatLateTasks
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
     * @param integer $lateTasks
     * @return StatLateTasks
     */
    public function setLateTasks($lateTasks)
    {
        $this->lateTasks = $lateTasks;

        return $this;
    }

    /**
     * Get lateTasks
     *
     * @return integer
     */
    public function getLateTasks()
    {
        return $this->lateTasks;
    }

    /**
     * Set ontimeTasks
     *
     * @param integer $ontimeTasks
     * @return StatLateTasks
     */
    public function setOntimeTasks($ontimeTasks)
    {
        $this->ontimeTasks = $ontimeTasks;

        return $this;
    }

    /**
     * Get ontimeTasks
     *
     * @return integer
     */
    public function getOntimeTasks()
    {
        return $this->ontimeTasks;
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
