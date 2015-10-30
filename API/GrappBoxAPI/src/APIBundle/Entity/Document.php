<?php

namespace APIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * Document
 */
class Document
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
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $hash;


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
     * @return Document
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
     * Set path
     *
     * @param string $path
     * @return Document
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set hash
     *
     * @param string $hash
     * @return Document
     */
    public function setHash($hash)
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * Get hash
     *
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
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
