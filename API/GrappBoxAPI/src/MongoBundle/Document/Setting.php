<?php

namespace MongoBundle\Document;



/**
 * MongoBundle\Document\Setting
 */
class Setting
{
    /**
     * @var $id
     */
    protected $id;

    /**
     * @var int $userId
     */
    protected $userId;

    /**
     * @var int $bugAlert
     */
    protected $bugAlert;

    /**
     * @var int $eventAlert
     */
    protected $eventAlert;

    /**
     * @var int $assignAlert
     */
    protected $assignAlert;

    /**
     * @var int $lateAlert
     */
    protected $lateAlert;

    /**
     * @var int $alertFrequency
     */
    protected $alertFrequency;


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
     * Set userId
     *
     * @param int $userId
     * @return self
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * Get userId
     *
     * @return int $userId
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set bugAlert
     *
     * @param int $bugAlert
     * @return self
     */
    public function setBugAlert($bugAlert)
    {
        $this->bugAlert = $bugAlert;
        return $this;
    }

    /**
     * Get bugAlert
     *
     * @return int $bugAlert
     */
    public function getBugAlert()
    {
        return $this->bugAlert;
    }

    /**
     * Set eventAlert
     *
     * @param int $eventAlert
     * @return self
     */
    public function setEventAlert($eventAlert)
    {
        $this->eventAlert = $eventAlert;
        return $this;
    }

    /**
     * Get eventAlert
     *
     * @return int $eventAlert
     */
    public function getEventAlert()
    {
        return $this->eventAlert;
    }

    /**
     * Set assignAlert
     *
     * @param int $assignAlert
     * @return self
     */
    public function setAssignAlert($assignAlert)
    {
        $this->assignAlert = $assignAlert;
        return $this;
    }

    /**
     * Get assignAlert
     *
     * @return int $assignAlert
     */
    public function getAssignAlert()
    {
        return $this->assignAlert;
    }

    /**
     * Set lateAlert
     *
     * @param int $lateAlert
     * @return self
     */
    public function setLateAlert($lateAlert)
    {
        $this->lateAlert = $lateAlert;
        return $this;
    }

    /**
     * Get lateAlert
     *
     * @return int $lateAlert
     */
    public function getLateAlert()
    {
        return $this->lateAlert;
    }

    /**
     * Set alertFrequency
     *
     * @param int $alertFrequency
     * @return self
     */
    public function setAlertFrequency($alertFrequency)
    {
        $this->alertFrequency = $alertFrequency;
        return $this;
    }

    /**
     * Get alertFrequency
     *
     * @return int $alertFrequency
     */
    public function getAlertFrequency()
    {
        return $this->alertFrequency;
    }
}