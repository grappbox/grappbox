<?php

namespace GrappboxBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TimelineType
 */
class TimelineType
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
     * @return TimelineType
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

    public function objectToArray()
    {
      return array(
        "id" => $this->id,
        "name" => $this->name
      );
    }
}
