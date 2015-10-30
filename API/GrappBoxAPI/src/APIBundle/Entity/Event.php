<?php

namespace APIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * Event
 */
class Event implements \Serializable
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $creatorId;

    /**
     * @var integer
     */
    private $projectId;

    /**
     * @var integer
     */
    private $typeId;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;

    /**
     * @var \DateTime
     */
    private $beginDate;

    /**
     * @var \DateTime
     */
    private $endDate;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var \DateTime
     */
    private $deletedAt;

    /**
     * @var \APIBundle\Entity\Project
     */
    private $projects;

    /**
     * @var \APIBundle\Entity\EventType
     */
    private $eventtypes;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $eventusers;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->eventusers = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set creatorId
     *
     * @param integer $creatorId
     * @return Event
     */
    public function setCreatorId($creatorId)
    {
        $this->creatorId = $creatorId;

        return $this;
    }

    /**
     * Get creatorId
     *
     * @return integer
     */
    public function getCreatorId()
    {
        return $this->creatorId;
    }

    /**
     * Set projectId
     *
     * @param integer $projectId
     * @return Event
     */
    public function setProjectId($projectId)
    {
        $this->projectId = $projectId;

        return $this;
    }

    /**
     * Get projectId
     *
     * @return integer
     */
    public function getProjectId()
    {
        return $this->projectId;
    }

    /**
     * Set typeId
     *
     * @param integer $typeId
     * @return Event
     */
    public function setTypeId($typeId)
    {
        $this->typeId = $typeId;

        return $this;
    }

    /**
     * Get typeId
     *
     * @return integer
     */
    public function getTypeId()
    {
        return $this->typeId;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Event
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Event
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set beginDate
     *
     * @param \DateTime $beginDate
     * @return Event
     */
    public function setBeginDate($beginDate)
    {
        $this->beginDate = $beginDate;

        return $this;
    }

    /**
     * Get beginDate
     *
     * @return \DateTime
     */
    public function getBeginDate()
    {
        return $this->beginDate;
    }

    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     * @return Event
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate
     *
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Event
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set deletedAt
     *
     * @param \DateTime $deletedAt
     * @return Event
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Get deletedAt
     *
     * @return \DateTime
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * Set projects
     *
     * @param \APIBundle\Entity\Project $projects
     * @return Event
     */
    public function setProjects(\APIBundle\Entity\Project $projects = null)
    {
        $this->projects = $projects;

        return $this;
    }

    /**
     * Get projects
     *
     * @return \APIBundle\Entity\Project
     */
    public function getProjects()
    {
        return $this->projects;
    }

    /**
     * Set eventtypes
     *
     * @param \APIBundle\Entity\EventType $eventtypes
     * @return Event
     */
    public function setEventtypes(\APIBundle\Entity\EventType $eventtypes = null)
    {
        $this->eventtypes = $eventtypes;

        return $this;
    }

    /**
     * Get eventtypes
     *
     * @return \APIBundle\Entity\EventType
     */
    public function getEventtypes()
    {
        return $this->eventtypes;
    }

    /**
     * Add eventusers
     *
     * @param \APIBundle\Entity\EventUser $eventusers
     * @return Event
     */
    public function addEventuser(\APIBundle\Entity\EventUser $eventusers)
    {
        $this->eventusers[] = $eventusers;

        return $this;
    }

    /**
     * Remove eventusers
     *
     * @param \APIBundle\Entity\EventUser $eventusers
     */
    public function removeEventuser(\APIBundle\Entity\EventUser $eventusers)
    {
        $this->eventusers->removeElement($eventusers);
    }

    /**
     * Get eventusers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEventusers()
    {
        return $this->eventusers;
    }

    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->creatorId,
            $this->projectId,
            $this->typeID,
            $this->eventtypes->name,
            $this->title,
            $this->description,
            $this->beginDate,
            $this->endDate,
            $this->createdAt,
            $this->deletedAt
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->creatorId,
            $this->projectId,
            $this->typeID,
            $this->eventtypes->name,
            $this->title,
            $this->description,
            $this->beginDate,
            $this->endDate,
            $this->createdAt,
            $this->deletedAt,
        ) = unserialize($serialized);
    }
}
