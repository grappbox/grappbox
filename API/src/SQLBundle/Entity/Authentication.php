<?php

namespace SQLBundle\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * Authentication
 */
class Authentication
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \SQLBundle\Entity\User
     */
    private $user;

    /**
     * @var string
     */
    private $macAddr;

    /**
     * @var string
     */
    private $deviceFlag;

    /**
     * @var string
     */
    private $deviceName;

    /**
     * @var string
     */
    private $token;

    /**
     * @var \DateTime
     */
    private $tokenValidity;

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
     * Set token
     *
     * @param string $token
     * @return Authentication
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
     * @param \DateTime $tokenValidity
     * @return Authentication
     */
    public function setTokenValidity($tokenValidity)
    {
        $this->tokenValidity = $tokenValidity;

        return $this;
    }

    /**
     * Get tokenValidity
     *
     * @return \DateTime
     */
    public function getTokenValidity()
    {
        return $this->tokenValidity;
    }

    /**
     * Set user
     *
     * @param \SQLBundle\Entity\User $user
     * @return Authentication
     */
    public function setUser(\SQLBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \SQLBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set deviceFlag
     *
     * @param string $deviceFlag
     * @return Authentication
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
     * @return Authentication
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
     * @return Authentication
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
