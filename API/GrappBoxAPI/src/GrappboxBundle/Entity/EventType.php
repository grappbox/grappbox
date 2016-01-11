<?php

namespace GrappboxBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EventType
 */
class EventType
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
     * @var \Doctrine\Common\Collections\Collection
     */
    private $events;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->events = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return EventType
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
     * Add events
     *
     * @param \GrappboxBundle\Entity\Event $events
     * @return EventType
     */
    public function addEvent(\GrappboxBundle\Entity\Event $events)
    {
        $this->events[] = $events;

        return $this;
    }

    /**
     * Remove events
     *
     * @param \GrappboxBundle\Entity\Event $events
     */
    public function removeEvent(\GrappboxBundle\Entity\Event $events)
    {
        $this->events->removeElement($events);
    }

    /**
     * Get events
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEvents()
    {
        return $this->events;
    }

    public function objectToArray()
    {
        return array(
            'id' => $this->id,
            'name' => $this->name
        );
    }
}
