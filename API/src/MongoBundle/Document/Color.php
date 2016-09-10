<?php

namespace MongoBundle\Document;

/**
 * MongoBundle\Document\Color
 */
class Color
{
    /**
     * @var id
     */
    protected $id;

    /**
     * @var string $color
     */
    protected $color;

    /**
     * @var MongoBundle\Document\User
     */
    protected $user;

    /**
     * @var MongoBundle\Document\Project
     */
    protected $project;


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
     * @return string $color
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * Set user
     *
     * @param MongoBundle\Document\User  $user
     * @return self
     */
    public function setUser(\MongoBundle\Document\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return MongoBundle\Document\User $user
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set project
     *
     * @param MongoBundle\Document\Project $project
     * @return self
     */
    public function setProject(\MongoBundle\Document\Project $project)
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
