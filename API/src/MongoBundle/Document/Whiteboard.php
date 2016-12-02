<?php

namespace MongoBundle\Document;



/**
 * MongoBundle\Document\Whiteboard
 */
class Whiteboard
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
     * @var date $updatedAt
     */
    protected $updatedAt;

    /**
     * @var date $createdAt
     */
    protected $createdAt;

    /**
     * @var date $deletedAt
     */
    protected $deletedAt;

    /**
     * @var MongoBundle\Document\WhiteboardObject
     */
    protected $objects = array();

    /**
     * @var MongoBundle\Document\WhiteboardPerson
     */
    private $persons = array();

    /**
     * @var MongoBundle\Document\Project
     */
    protected $projects;

    /**
     * @var MongoBundle\Document\User
     */
    private $creator_user;

    /**
     * @var MongoBundle\Document\User
     */
    private $updator_user;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->objects = new \Doctrine\Common\Collections\ArrayCollection();
        $this->persons = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function objectToArray()
    {
        return array(
            'id' => $this->id,
            'projectId' => $this->getProjects()->getId(),
            'user' => array("id" => $this->creator_user->getId(), "firstname" => $this->creator_user->getFirstname(), "lastname" => $this->creator_user->getLastname()),
            'name' => $this->name,
            'updator' => array("id" => $this->creator_user->getId(), "firstname" => $this->creator_user->getFirstname(), "lastname" => $this->creator_user->getLastname()),
            'updatedAt' => $this->updatedAt ? $this->updatedAt->format('Y-m-d H:i:s') : null,
            'createdAt' => $this->createdAt ? $this->createdAt->format('Y-m-d H:i:s') : null,
            'deletedAt' => $this->deletedAt ? $this->deletedAt->format('Y-m-d H:i:s') : null
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

    /**
     * Set updatedAt
     *
     * @param date $updatedAt
     * @return self
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return date $updatedAt
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set createdAt
     *
     * @param date $createdAt
     * @return self
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return date $createdAt
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set deletedAt
     *
     * @param date $deletedAt
     * @return self
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Get deletedAt
     *
     * @return date $deletedAt
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * Add object
     *
     * @param MongoBundle\Document\WhiteboardObject $object
     * @return self
     */
    public function addObject( $object)
    {
        $this->objects[] = $object;

        return $this;
    }

    /**
     * Remove object
     *
     * @param MongoBundle\Document\WhiteboardObject $object
     */
    public function removeObject( $object)
    {
        $this->objects->removeElement($object);
    }

    /**
     * Get objects
     *
     * @return \Doctrine\Common\Collections\Collection $objects
     */
    public function getObjects()
    {
        return $this->objects;
    }

    /**
     * Add persons
     *
     * @param MongoBundle\Document\WhiteboardPerson $persons
     * @return Whiteboard
     */
    public function addPerson($persons)
    {
        $this->persons[] = $persons;

        return $this;
    }

    /**
     * Remove persons
     *
     * @param MongoBundle\Document\WhiteboardPerson $persons
     */
    public function removePerson($persons)
    {
        $this->persons->removeElement($persons);
    }

    /**
     * Get persons
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPersons()
    {
        return $this->persons;
    }

    /**
     * Set projects
     *
     * @param MongoBundle\Document\Project $projects
     * @return self
     */
    public function setProjects( $projects)
    {
        $this->projects = $projects;

        return $this;
    }

    /**
     * Get projects
     *
     * @return MongoBundle\Document\Project $projects
     */
    public function getProjects()
    {
        return $this->projects;
    }

    /**
     * Set creator_user
     *
     * @param MongoBundle\Document\User $creatorUser
     * @return Whiteboard
     */
    public function setCreatorUser($creatorUser)
    {
        $this->creator_user = $creatorUser;

        return $this;
    }

    /**
     * Get creator_user
     *
     * @return MongoBundle\Document\User
     */
    public function getCreatorUser()
    {
        return $this->creator_user;
    }

    /**
     * Set updator_user
     *
     * @param MongoBundle\Document\User $updatorUser
     * @return Whiteboard
     */
    public function setUpdatorUser($updatorUser)
    {
        $this->updator_user = $updatorUser;

        return $this;
    }

    /**
     * Get updator_user
     *
     * @return MongoBundle\Document\User
     */
    public function getUpdatorUser()
    {
        return $this->updator_user;
    }
}
