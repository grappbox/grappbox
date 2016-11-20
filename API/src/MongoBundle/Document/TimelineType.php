<?php

namespace MongoBundle\Document;



/**
 * MongoBundle\Document\TimelineType
 */
class TimelineType
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

    public function objectToArray()
    {
      return array(
        "id" => $this->id,
        "name" => $this->name
      );
    }
}
