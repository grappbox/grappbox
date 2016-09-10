<?php

namespace MongoBundle\Document;



/**
 * MongoBundle\Document\StatuserWorkingCharge
 */
class StatUserWorkingCharge
{
    /**
     * @var id
     */
    protected $id;

    /**
     * @var string
     */
    protected $user;

    /**
     * @var int
     */
    protected $charge;

    /**
     * @var MongoBundle\Document\Project
     */
    protected $project;

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
     * @return id
     */
    public function getId()
    {
        return $this->id;
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
     * Set charge
     *
     * @param int $charge
     * @return self
     */
    public function setCharge($charge)
    {
        $this->charge = $charge;

        return $this;
    }

    /**
     * Get charge
     *
     * @return int
     */
    public function getCharge()
    {
        return $this->charge;
    }

    /**
     * Set project
     *
     * @param MongoBundle\Document\Project $project
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
