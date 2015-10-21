<?php

namespace APIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EventUser
 */
class EventUser
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $userId;

    /**
     * @var integer
     */
    private $eventId;

    /**
     * @var \APIBundle\Entity\Event
     */
    private $events;

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
     * Set userId
     *
     * @param integer $userId
     * @return EventUser
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return integer 
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set eventId
     *
     * @param integer $eventId
     * @return EventUser
     */
    public function setEventId($eventId)
    {
        $this->eventId = $eventId;

        return $this;
    }

    /**
     * Get eventId
     *
     * @return integer 
     */
    public function getEventId()
    {
        return $this->eventId;
    }

    /**
     * Set events
     *
     * @param \APIBundle\Entity\Event $events
     * @return EventUser
     */
    public function setEvents(\APIBundle\Entity\Event $events = null)
    {
        $this->events = $events;

        return $this;
    }

    /**
     * Get events
     *
     * @return \APIBundle\Entity\Event 
     */
    public function getEvents()
    {
        return $this->events;
    }
}
