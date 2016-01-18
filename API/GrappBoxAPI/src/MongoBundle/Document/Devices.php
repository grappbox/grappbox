<?php

namespace MongoBundle\Document;



/**
 * MongoBundle\Document\Devices
 */
class Devices
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
     * @var string $type
     */
    protected $type;

    /**
     * @var string $token
     */
    protected $token;

    /**
     * @var MongoBundle\Document\User
     */
    protected $user;


    public function objectToArray()
    {
      return array(
        "id" => $this->id,
        "user" => array("id" => $this->user->getId(), "firstname" => $this->user->getFirstname(), "lastname" => $this->user->getLastName()),
        "name" => $this->name,
        "token" => $this->token,
        "type" => $this->type
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
     * Set type
     *
     * @param string $type
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Get type
     *
     * @return string $type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set token
     *
     * @param string $token
     * @return self
     */
    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    /**
     * Get token
     *
     * @return string $token
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set user
     *
     * @param MongoBundle\Document\User $user
     * @return self
     */
    public function setUser(\MongoBundle\Document\User $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Get user
     *
     * @return MongoBundle\Document\User $user
     */
    public function getUser()
    {
        return $this->user;
    }
}