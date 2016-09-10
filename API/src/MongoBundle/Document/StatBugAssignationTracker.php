<?php

namespace MongoBundle\Document;



/**
 * MongoBundle\Document\StatBugAssignationTracker
 */
class StatBugAssignationTracker
{
    /**
     * @var id
     */
    private $id;

    /**
     * @var int
     */
    private $assignedBugs;

    /**
     * @var int
     */
    private $unassignedBugs;

    /**
     * @var MongoBundle\Document\Project
     */
    private $project;


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
     * Set assignedBugs
     *
     * @param int $assignedBugs
     * @return self
     */
    public function setAssignedBugs($assignedBugs)
    {
        $this->assignedBugs = $assignedBugs;

        return $this;
    }

    /**
     * Get assignedBugs
     *
     * @return int
     */
    public function getAssignedBugs()
    {
        return $this->assignedBugs;
    }

    /**
     * Set unassignedBugs
     *
     * @param int $unassignedBugs
     * @return self
     */
    public function setUnassignedBugs($unassignedBugs)
    {
        $this->unassignedBugs = $unassignedBugs;

        return $this;
    }

    /**
     * Get unassignedBugs
     *
     * @return int
     */
    public function getUnassignedBugs()
    {
        return $this->unassignedBugs;
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
