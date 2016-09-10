<?php

namespace MongoBundle\Document;



/**
 * MongoBundle\Document\EventType
 */
class EventType
{
    /**
     * @var $id
     */
    protected $id;

    /**
     * @var string $name
     */
    protected $name;

    /**
     * @var MongoBundle\Document\Event
     */
    protected $events = array();

    public function __construct()
    {
        $this->events = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Add event
     *
     * @param MongoBundle\Document\Event $event
     */
    public function addEvent( $event)
    {
        $this->events[] = $event;
    }

    /**
     * Remove event
     *
     * @param MongoBundle\Document\Event $event
     */
    public function removeEvent( $event)
    {
        $this->events->removeElement($event);
    }

    /**
     * Get events
     *
     * @return \Doctrine\Common\Collections\Collection $events
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
