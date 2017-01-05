<?php

namespace SQLBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * StatLateTasks
 */
class StatLateTasks
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var \DateTime
     */
    protected $date;

    /**
     * @var string
     */
    protected $role;

    /**
     * @var integer
     */
    protected $lateTasks;

    /**
     * @var integer
     */
    protected $ontimeTasks;

    /**
     * @var \SQLBundle\Entity\Project
     */
    protected $project;

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
        "role" => $this->role,
        "date" => $this->date ? $this->date->format('Y-m-d H:i:s') : null,
        "lateTasks" => $this->lateTasks,
        "ontimeTasks" => $this->ontimeTasks,
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
     * @param \SQLBundle\Entity\Project $project
     * @return Project
     */
    public function setProject(\SQLBundle\Entity\Project $project = null)
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
     * @return StatLateTasks
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
