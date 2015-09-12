<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * timeline_messages
 *
 * @ORM\Table(name="timeline_messages")
 * @ORM\Entity
 */
class TimelineMessage
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
     * @ORM\Column(name="timeline_id", type="integer")
     */
    private $timelineId;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="text")
     */
    private $message;


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
     * @return timeline_message
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
     * Set timelineId
     *
     * @param integer $timelineId
     * @return timeline_message
     */
    public function setTimelineId($timelineId)
    {
        $this->timelineId = $timelineId;

        return $this;
    }

    /**
     * Get timelineId
     *
     * @return integer
     */
    public function getTimelineId()
    {
        return $this->timelineId;
    }

    /**
     * Set message
     *
     * @param string $message
     * @return timeline_message
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }
}
