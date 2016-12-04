<?php

namespace MongoBundle\Document;



/**
 * MongoBundle\Document\StatBugsUsersRepartition
 */
class StatBugsUsersRepartition
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var integer
     */
    protected $value;

    /**
     * @var float
     */
    protected $percentage;

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
        "value" => $this->value,
        "percentage" => $this->percentage
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
     * Set value
     *
     * @param int $value
     * @return self
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set percentage
     *
     * @param float $percentage
     * @return self
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
     * @param MongoBundle\Document\Project $project
     * @return self
     */
    public function setProject($project)
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
