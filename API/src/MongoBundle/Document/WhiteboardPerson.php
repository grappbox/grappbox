<?php

namespace MongoBundle\Document;

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
     * @var MongoBundle\Document\Whiteboard
     */
    private $whiteboard;

    /**
     * @var MongoBundle\Document\User
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
     * @param MongoBundle\Document\Whiteboard $whiteboard
     * @return self
     */
    public function setWhiteboard($whiteboard)
    {
        $this->whiteboard = $whiteboard;

        return $this;
    }

    /**
     * Get whiteboard
     *
     * @return MongoBundle\Document\Whiteboard
     */
    public function getWhiteboard()
    {
        return $this->whiteboard;
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
