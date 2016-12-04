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
     * @var int
     */
    protected $charge;

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
