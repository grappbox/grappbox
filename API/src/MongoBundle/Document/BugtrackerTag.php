<?php

namespace MongoBundle\Document;


/**
 * BugtrackerTag
 */
class BugtrackerTag
{
    /**
     * @var $id
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var MongoBundle\Document\Project
     */
    protected $project;

    /**
     * @var MongoBundle\Document\Bug
     */
    protected $bugs = array();

    /**
     * @var string
     */
    protected $color;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->bugs = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function objectToArray()
    {
      return array(
        "id" => $this->id,
        "name" => $this->name,
        "color" => $this->color
      );
    }

    /**
     * Get id
     *
     * @return id $id
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
     * Add bugs
     *
     * @param MongoBundle\Document\Bug $bugs
     * @return self
     */
    public function addBug($bugs)
    {
        $this->bugs[] = $bugs;
        return $this;
    }

    /**
     * Remove bugs
     *
     * @param MongoBundle\Document\Bug $bugs
     */
    public function removeBug($bugs)
    {
        $this->bugs->removeElement($bugs);
    }

    /**
     * Get bugs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBugs()
    {
        return $this->bugs;
    }

    /**
     * Set color
     *
     * @param string $color
     * @return self
     */
    public function setColor($color)
    {
        $this->color = $color;
        return $this;
    }

    /**
     * Get color
     *
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }
}
