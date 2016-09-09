<?php

namespace SQLBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Setting
 */
class Setting
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $userId;

    /**
     * @var integer
     */
    private $bugAlert;

    /**
     * @var integer
     */
    private $eventAlert;

    /**
     * @var integer
     */
    private $assignAlert;

    /**
     * @var integer
     */
    private $lateAlert;

    /**
     * @var integer
     */
    private $alertFrequency;


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
     * Set userId
     *
     * @param integer $userId
     * @return Setting
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
     * Set bugAlert
     *
     * @param integer $bugAlert
     * @return Setting
     */
    public function setBugAlert($bugAlert)
    {
        $this->bugAlert = $bugAlert;

        return $this;
    }

    /**
     * Get bugAlert
     *
     * @return integer
     */
    public function getBugAlert()
    {
        return $this->bugAlert;
    }

    /**
     * Set eventAlert
     *
     * @param integer $eventAlert
     * @return Setting
     */
    public function setEventAlert($eventAlert)
    {
        $this->eventAlert = $eventAlert;

        return $this;
    }

    /**
     * Get eventAlert
     *
     * @return integer
     */
    public function getEventAlert()
    {
        return $this->eventAlert;
    }

    /**
     * Set assignAlert
     *
     * @param integer $assignAlert
     * @return Setting
     */
    public function setAssignAlert($assignAlert)
    {
        $this->assignAlert = $assignAlert;

        return $this;
    }

    /**
     * Get assignAlert
     *
     * @return integer
     */
    public function getAssignAlert()
    {
        return $this->assignAlert;
    }

    /**
     * Set lateAlert
     *
     * @param integer $lateAlert
     * @return Setting
     */
    public function setLateAlert($lateAlert)
    {
        $this->lateAlert = $lateAlert;

        return $this;
    }

    /**
     * Get lateAlert
     *
     * @return integer
     */
    public function getLateAlert()
    {
        return $this->lateAlert;
    }

    /**
     * Set alertFrequency
     *
     * @param integer $alertFrequency
     * @return Setting
     */
    public function setAlertFrequency($alertFrequency)
    {
        $this->alertFrequency = $alertFrequency;

        return $this;
    }

    /**
     * Get alertFrequency
     *
     * @return integer
     */
    public function getAlertFrequency()
    {
        return $this->alertFrequency;
    }
}
