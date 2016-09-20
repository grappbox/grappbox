<?php

namespace SQLBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tag
 */
class BugtrackerTag
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var \SQLBundle\Entity\Project
     */
    private $project;


    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $bugs;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->bugs = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set name
     *
     * @param string $name
     * @return Tag
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
     * @param \SQLBundle\Entity\Project $project
     * @return Tag
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
     * Add bugs
     *
     * @param \SQLBundle\Entity\Bug $bugs
     * @return Tag
     */
    public function addBug(\SQLBundle\Entity\Bug $bugs)
    {
        $this->bugs[] = $bugs;

        return $this;
    }

    /**
     * Remove bugs
     *
     * @param \SQLBundle\Entity\Bug $bugs
     */
    public function removeBug(\SQLBundle\Entity\Bug $bugs)
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

    public function objectToArray()
    {
      return array(
        "id" => $this->id,
        "name" => $this->name
      );
    }
    /**
     * @var string
     */
    private $color;


    /**
     * Set color
     *
     * @param string $color
     * @return BugtrackerTag
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
