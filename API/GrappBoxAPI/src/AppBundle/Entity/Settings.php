<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Settings
 *
 * @ORM\Table(name="settings")
 * @ORM\Entity
 */
class Settings
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="integer")
     */
    private $userId;

    /**
     * @var integer
     *
     * @ORM\Column(name="bug_alert", type="smallint")
     */
    private $bugAlert;

    /**
     * @var integer
     *
     * @ORM\Column(name="event_alert", type="smallint")
     */
    private $eventAlert;

    /**
     * @var integer
     *
     * @ORM\Column(name="assign_alert", type="smallint")
     */
    private $assignAlert;

    /**
     * @var integer
     *
     * @ORM\Column(name="late_alert", type="smallint")
     */
    private $lateAlert;

    /**
     * @var integer
     *
     * @ORM\Column(name="alert_frequency", type="smallint")
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
     * @return Settings
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
     * @return Settings
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
     * @return Settings
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
     * @return Settings
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
     * @return Settings
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
     * @return Settings
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
