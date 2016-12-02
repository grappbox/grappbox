<?php

namespace SQLBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * WhiteboardPerson
 */
class WhiteboardPerson
{
    
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \SQLBundle\Entity\Whiteboard
     */
    private $whiteboard;

    /**
     * @var \SQLBundle\Entity\User
     */
    private $user;


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
     * Set whiteboard
     *
     * @param \SQLBundle\Entity\Whiteboard $whiteboard
     * @return WhiteboardPerson
     */
    public function setWhiteboard(\SQLBundle\Entity\Whiteboard $whiteboard = null)
    {
        $this->whiteboard = $whiteboard;

        return $this;
    }

    /**
     * Get whiteboard
     *
     * @return \SQLBundle\Entity\Whiteboard 
     */
    public function getWhiteboard()
    {
        return $this->whiteboard;
    }

    /**
     * Set user
     *
     * @param \SQLBundle\Entity\User $user
     * @return WhiteboardPerson
     */
    public function setUser(\SQLBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \SQLBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }
}
