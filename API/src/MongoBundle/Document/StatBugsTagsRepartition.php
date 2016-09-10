<?php

namespace MongoBundle\Document;



/**
 * MongoBundle\Document\StatBugsTagsRepartition
 */
class StatBugsTagsRepartition
{
    /**
     * @var id
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var int
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


    public function objectToArray()
    {
      return array(
        "name" => $this->name,
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
     * Set name
     *
     * @param string $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
     * @return Sself
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
