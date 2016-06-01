<?php

namespace GrappboxBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * StatuserWorkingCharge
 */
class StatUserWorkingCharge
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
    private $charge;

    /**
     * @var \GrappboxBundle\Entity\Project
     */
    private $project;

    public function objectToArray()
    {
      return array(
        "user" => $this->user,
        "charge" => $this->charge
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
     * @return StatUserWorkingCharge
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
     * Set charge
     *
     * @param integer $charge
     * @return StatUserWorkingCharge
     */
    public function setCharge($charge)
    {
        $this->charge = $charge;

        return $this;
    }

    /**
     * Get charge
     *
     * @return integer
     */
    public function getCharge()
    {
        return $this->charge;
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
