<?php

namespace APIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * Whiteboard
 */
class Whiteboard
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $projectId;

    /**
     * @var integer
     */
    private $userId;

    /**
     * @var string
     */
    private $name;

    /**
     * @var integer
     */
    private $updatorId;

    /**
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var \DateTime
     */
    private $deletedAt;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $projects;


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
     * Set projectId
     *
     * @param integer $projectId
     * @return Whiteboard
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
     * Set userId
     *
     * @param integer $userId
     * @return Whiteboard
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
     * Set name
     *
     * @param string $name
     * @return Whiteboard
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
     * Set updatorId
     *
     * @param integer $updatorId
     * @return Whiteboard
     */
    public function setUpdatorId($updatorId)
    {
        $this->updatorId = $updatorId;

        return $this;
    }

    /**
     * Get updatorId
     *
     * @return integer
     */
    public function getUpdatorId()
    {
        return $this->updatorId;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return Whiteboard
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Whiteboard
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
     * @return Whiteboard
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
     * Serialize the object
     *
     * @return json encoded array
     */
    public function serializeMe()
    {
      $encoders = array(new XmlEncoder(), new JsonEncoder());
      $normalizers = array(new ObjectNormalizer());

      $serializer = new Serializer($normalizers, $encoders);

      return $serializer->serialize($this, 'json');
    }
}
