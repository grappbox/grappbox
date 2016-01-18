<?php

namespace MongoBundle\Document;



/**
 * MongoBundle\Document\BugState
 */
class BugState
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
     * Get object content into array
     *
     * @return array
     */
    public function objectToArray()
    {
      return array(
        "id" => $this->id,
        "name" => $this->name,
      );
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
}
