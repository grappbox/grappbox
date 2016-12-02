<?php

namespace MongoBundle\Document;


/**
 * MongoBundle\Document\Bug
 */
class Authentication
{
    /**
     * @var $id
     */
    protected $id;

    /**
     * @var MongoBundle\Document\User
     */
    protected $user;

    /**
     * @var string $macAddr
     */
    protected $macAddr;

    /**
     * @var string $deviceFlag
     */
    protected $deviceFlag;

    /**
     * @var string $deviceName
     */
    protected $deviceName;

    /**
     * @var string $token
     */
    protected $token;

    /**
     * @var date $tokenValidity
     */
    protected $tokenValidity;

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
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set tokenValidity
     *
     * @param date $tokenValidity
     * @return self
     */
    public function setTokenValidity($tokenValidity)
    {
        $this->tokenValidity = $tokenValidity;

        return $this;
    }

    /**
     * Get tokenValidity
     *
     * @return date
     */
    public function getTokenValidity()
    {
        return $this->tokenValidity;
    }

    /**
     * Set user
     *
     * @param MongoBundle\Document\User $user
     * @return self
     */
    public function setUser($user)
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

    /**
     * Set deviceFlag
     *
     * @param string $deviceFlag
     * @return self
     */
    public function setDeviceFlag($deviceFlag)
    {
        $this->deviceFlag = $deviceFlag;

        return $this;
    }

    /**
     * Get deviceFlag
     *
     * @return string
     */
    public function getDeviceFlag()
    {
        return $this->deviceFlag;
    }

    /**
     * Set macAddr
     *
     * @param string $macAddr
     * @return self
     */
    public function setMacAddr($macAddr)
    {
        $this->macAddr = $macAddr;

        return $this;
    }

    /**
     * Get macAddr
     *
     * @return string
     */
    public function getMacAddr()
    {
        return $this->macAddr;
    }

    /**
     * Set deviceName
     *
     * @param string $deviceName
     * @return self
     */
    public function setDeviceName($deviceName)
    {
        $this->deviceName = $deviceName;

        return $this;
    }

    /**
     * Get deviceName
     *
     * @return string
     */
    public function getDeviceName()
    {
        return $this->deviceName;
    }

}
