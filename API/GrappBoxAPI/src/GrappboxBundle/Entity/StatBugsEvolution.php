<?php

namespace GrappboxBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * StatBugsEvolution
 */
class StatBugsEvolution
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
     * @var integer
     */
    private $createdBugs;

    /**
     * @var integer
     */
    private $closedBugs;

    /**
     * @var \GrappboxBundle\Entity\Project
     */
    private $project;

    public function objectToArray()
    {
        return array(
          "date" => $this->date,
          "createdBugs" => $this->createdBugs,
          "closedBugs" => $this->closedBugs
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
     * @return StatBugsEvolution
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
     * Set createdBugs
     *
     * @param integer $createdBugs
     * @return StatBugsEvolution
     */
    public function setCreatedBugs($createdBugs)
    {
        $this->createdBugs = $createdBugs;

        return $this;
    }

    /**
     * Get createdBugs
     *
     * @return integer
     */
    public function getCreatedBugs()
    {
        return $this->createdBugs;
    }

    /**
     * Set closedBugs
     *
     * @param integer $closedBugs
     * @return StatBugsEvolution
     */
    public function setClosedBugs($closedBugs)
    {
        $this->closedBugs = $closedBugs;

        return $this;
    }

    /**
     * Get closedBugs
     *
     * @return integer
     */
    public function getClosedBugs()
    {
        return $this->closedBugs;
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
