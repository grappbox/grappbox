<?php

namespace SQLBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * StatBugsUsersRepartition
 */
class StatBugsUsersRepartition
{
    /**
     * @var integer
     */
    private $id;

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
     * Set value
     *
     * @param integer $value
     * @return StatBugsUsersRepartition
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
     * @return StatBugsUsersRepartition
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
     * @return StatBugsUsersRepartition
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
