<?php

namespace GrappboxBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * StatBugAssignationTracker
 */
class StatBugAssignationTracker
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $assignedBugs;

    /**
     * @var integer
     */
    private $unassignedBugs;

    /**
     * @var \GrappboxBundle\Entity\Project
     */
    private $project;


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
     * Set assignedBugs
     *
     * @param integer $assignedBugs
     * @return StatBugAssignationTracker
     */
    public function setAssignedBugs($assignedBugs)
    {
        $this->assignedBugs = $assignedBugs;

        return $this;
    }

    /**
     * Get assignedBugs
     *
     * @return integer
     */
    public function getAssignedBugs()
    {
        return $this->assignedBugs;
    }

    /**
     * Set unassignedBugs
     *
     * @param integer $unassignedBugs
     * @return StatBugAssignationTracker
     */
    public function setUnassignedBugs($unassignedBugs)
    {
        $this->unassignedBugs = $unassignedBugs;

        return $this;
    }

    /**
     * Get unassignedBugs
     *
     * @return integer
     */
    public function getUnassignedBugs()
    {
        return $this->unassignedBugs;
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
