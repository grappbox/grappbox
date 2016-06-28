<?php

namespace MongoBundle\Document;



use Symfony\Component\Security\Core\User\UserInterface;

/**
 * MongoBundle\Document\User
 */
class User implements UserInterface
{
    /**
     * @var $id
     */
    protected $id;

    /**
     * @var string $firstname
     */
    protected $firstname;

    /**
     * @var string $lastname
     */
    protected $lastname;

    /**
     * @var date $birthday
     */
    protected $birthday;

    /**
     * @var string $avatar
     */
    protected $avatar;

    /**
     * @var string $password
     */
    protected $password;

    /**
     * @var string $email
     */
    protected $email;

    /**
     * @var string $phone
     */
    protected $phone;

    /**
     * @var string $country
     */
    protected $country;

    /**
     * @var string $linkedin
     */
    protected $linkedin;

    /**
     * @var string $viadeo
     */
    protected $viadeo;

    /**
     * @var string $twitter
     */
    protected $twitter;

    /**
     * @var string $token
     */
    protected $token;

    /**
     * @var date $tokenValidity
     */
    protected $tokenValidity;

    /**
     * @var MongoBundle\Document\Bug
     */
    protected $bug_creator = array();

    /**
     * @var MongoBundle\Document\TimelineMessage
     */
    protected $message_creator = array();

    /**
     * @var MongoBundle\Document\Event
     */
    protected $event_creator = array();

    /**
     * @var MongoBundle\Document\Project
     */
    protected $project_creator = array();

    /**
     * @var MongoBundle\Document\Gantt
     */
    protected $gantt_creator = array();

    /**
     * @var MongoBundle\Document\Gantt
     */
    protected $gantt_updator = array();

    /**
     * @var MongoBundle\Document\Task
     */
    protected $task_creator;

    /**
     * @var MongoBundle\Document\Notification
     */
    protected $notifications = array();

    /**
     * @var MongoBundle\Document\Project
     */
    protected $projects = array();

    /**
     * @var MongoBundle\Document\Event
     */
    protected $events = array();

    /**
     * @var MongoBundle\Document\Ressources
     */
    protected $ressources = array();

    /**
     * @var MongoBundle\Document\Color
     */
    protected $colors;

    /**
     * Constructor
    */
    public function __construct()
    {
      $this->bug_creator = new \Doctrine\Common\Collections\ArrayCollection();
      $this->message_creator = new \Doctrine\Common\Collections\ArrayCollection();
      $this->event_creator = new \Doctrine\Common\Collections\ArrayCollection();
      $this->project_creator = new \Doctrine\Common\Collections\ArrayCollection();
      $this->gantt_creator = new \Doctrine\Common\Collections\ArrayCollection();
      $this->gantt_updator = new \Doctrine\Common\Collections\ArrayCollection();
      $this->notifications = new \Doctrine\Common\Collections\ArrayCollection();
      $this->projects = new \Doctrine\Common\Collections\ArrayCollection();
      $this->events = new \Doctrine\Common\Collections\ArrayCollection();
      $this->tasks = new \Doctrine\Common\Collections\ArrayCollection();
    }


    public function getUsername()
    {
        return $this->email;
    }
    public function getSalt()
    {
       return null;
    }
    public function getRoles()
    {
        return array('ROLE_USER');
    }
    public function eraseCredentials()
    {
    }

    public function objectToArray()
    {
      return array(
          'id' => $this->id,
          'firstname' => $this->firstname,
          'lastname' => $this->lastname,
          'email' => $this->email,
          'token' => $this->token,
          'avatar' => $this->avatar
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
     * Set firstname
     *
     * @param string $firstname
     * @return self
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
        return $this;
    }

    /**
     * Get firstname
     *
     * @return string $firstname
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     * @return self
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
        return $this;
    }

    /**
     * Get lastname
     *
     * @return string $lastname
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set birthday
     *
     * @param date $birthday
     * @return self
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;
        return $this;
    }

    /**
     * Get birthday
     *
     * @return date $birthday
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * Set avatar
     *
     * @param string $avatar
     * @return self
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;
        return $this;
    }

    /**
     * Get avatar
     *
     * @return string $avatar
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return self
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Get password
     *
     * @return string $password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return self
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Get email
     *
     * @return string $email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return self
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * Get phone
     *
     * @return string $phone
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set country
     *
     * @param string $country
     * @return self
     */
    public function setCountry($country)
    {
        $this->country = $country;
        return $this;
    }

    /**
     * Get country
     *
     * @return string $country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set linkedin
     *
     * @param string $linkedin
     * @return self
     */
    public function setLinkedin($linkedin)
    {
        $this->linkedin = $linkedin;
        return $this;
    }

    /**
     * Get linkedin
     *
     * @return string $linkedin
     */
    public function getLinkedin()
    {
        return $this->linkedin;
    }

    /**
     * Set viadeo
     *
     * @param string $viadeo
     * @return self
     */
    public function setViadeo($viadeo)
    {
        $this->viadeo = $viadeo;
        return $this;
    }

    /**
     * Get viadeo
     *
     * @return string $viadeo
     */
    public function getViadeo()
    {
        return $this->viadeo;
    }

    /**
     * Set twitter
     *
     * @param string $twitter
     * @return self
     */
    public function setTwitter($twitter)
    {
        $this->twitter = $twitter;
        return $this;
    }

    /**
     * Get twitter
     *
     * @return string $twitter
     */
    public function getTwitter()
    {
        return $this->twitter;
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
     * @return date $tokenValidity
     */
    public function getTokenValidity()
    {
        return $this->tokenValidity;
    }

    /**
     * Add event_creator
     *
     * @param \MongoBundle\Document\Event $eventCreator
     */
    public function addEventCreator(\MongoBundle\Document\Event $eventCreator)
    {
        $this->event_creator[] = $eventCreator;
    }

    /**
     * Remove event_creator
     *
     * @param \MongoBundle\Document\Event $eventCreator
     */
    public function removeEventCreator(\MongoBundle\Document\Event $eventCreator)
    {
        $this->event_creator->removeElement($eventCreator);
    }

    /**
     * Get event_creator
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEventCreator()
    {
        return $this->event_creator;
    }

    /**
     * Add bug_creator
     *
     * @param \MongoBundle\Document\Bug $bugCreator
     */
    public function addBugCreator(\MongoBundle\Document\Bug $bugCreator)
    {
        $this->bug_creator[] = $bugCreator;
    }

    /**
     * Remove bug_creator
     *
     * @param \MongoBundle\Document\Bug $bugCreator
     */
    public function removeBugCreator(\MongoBundle\Document\Bug $bugCreator)
    {
        $this->bug_creator->removeElement($bugCreator);
    }

    /**
     * Get bug_creator
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBugCreator()
    {
        return $this->bug_creator;
    }


    /**
     * Add message_creator
     *
     * @param \MongoBundle\Document\TimelineMessage $messageCreator
     */
    public function addMessageCreator(\MongoBundle\Document\TimelineMessage $messageCreator)
    {
        $this->message_creator[] = $messageCreator;
    }

    /**
     * Remove message_creator
     * @param \MongoBundle\Document\TimelineMessage $messageCreator
     */
    public function removeMessageCreator(\MongoBundle\Document\TimelineMessage $messageCreator)
    {
        $this->message_creator->removeElement($messageCreator);
    }

    /**
     * Get message_creator
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMessageCreator()
    {
        return $this->message_creator;
    }

    /**
     * Add project_creator
     *
     * @param \MongoBundle\Document\Project $projectCreator
     */
    public function addProjectCreator(\MongoBundle\Document\Project $projectCreator)
    {
        $this->project_creator[] = $projectCreator;
    }

    /**
     * Remove project_creator
     *
     * @param \MongoBundle\Document\Project $projectCreator
     */
    public function removeProjectCreator(\MongoBundle\Document\Project $projectCreator)
    {
        $this->project_creator->removeElement($projectCreator);
    }

    /**
     * Get project_creator
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProjectCreator()
    {
        return $this->project_creator;
    }

    /**
     * Add gantt_creator
     *
     * @param \MongoBundle\Document\Gantt $ganttCreator
     */
    public function addGanttCreator(\MongoBundle\Document\Gantt $ganttCreator)
    {
        $this->gantt_creator[] = $ganttCreator;
    }

    /**
     * Remove gantt_creator
     *
     * @param \MongoBundle\Document\Gantt $ganttCreator
     */
    public function removeGanttCreator(\MongoBundle\Document\Gantt $ganttCreator)
    {
        $this->gantt_creator->removeElement($ganttCreator);
    }

    /**
     * Get gantt_creator
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGanttCreator()
    {
        return $this->gantt_creator;
    }

    /**
     * Add gantt_updator
     *
     * @param \MongoBundle\Document\Gantt $ganttUpdator
     */
    public function addGanttUpdator(\MongoBundle\Document\Gantt $ganttUpdator)
    {
        $this->gantt_updator[] = $ganttUpdator;
    }

    /**
     * Remove gantt_updator
     *
     * @param \MongoBundle\Document\Gantt $ganttUpdator
     */
    public function removeGanttUpdator(\MongoBundle\Document\Gantt $ganttUpdator)
    {
        $this->gantt_updator->removeElement($ganttUpdator);
    }

    /**
     * Get gantt_updator
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGanttUpdator()
    {
        return $this->gantt_updator;
    }

    /**
     * Add notifications
     *
     * @param \MongoBundle\Document\Notification $notifications
     */
    public function addNotification(\MongoBundle\Document\Notification $notifications)
    {
        $this->notifications[] = $notifications;
    }

    /**
     * Remove notifications
     *
     * @param \MongoBundle\Document\Notification $notifications
     */
    public function removeNotification(\MongoBundle\Document\Notification $notifications)
    {
        $this->notifications->removeElement($notifications);
    }

    /**
     * Get notifications
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNotifications()
    {
        return $this->notifications;
    }

    /**
     * Add project
     *
     * @param MongoBundle\Document\Project $project
     */
    public function addProject(\MongoBundle\Document\Project $project)
    {
        $this->projects[] = $project;
    }

    /**
     * Remove project
     *
     * @param MongoBundle\Document\Project $project
     */
    public function removeProject(\MongoBundle\Document\Project $project)
    {
        $this->projects->removeElement($project);
    }

    /**
     * Get projects
     *
     * @return \Doctrine\Common\Collections\Collection $projects
     */
    public function getProjects()
    {
        return $this->projects;
    }

    /**
     * Add event
     *
     * @param MongoBundle\Document\Event $event
     */
    public function addEvent(\MongoBundle\Document\Event $event)
    {
        $this->events[] = $event;
    }

    /**
     * Remove event
     *
     * @param MongoBundle\Document\Event $event
     */
    public function removeEvent(\MongoBundle\Document\Event $event)
    {
        $this->events->removeElement($event);
    }

    /**
     * Get events
     *
     * @return \Doctrine\Common\Collections\Collection $events
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * Add devices
     *
     * @param \MongoBundle\Document\Devices $devices
     */
    public function addDevice(\MongoBundle\Document\Devices $devices)
    {
        $this->devices[] = $devices;
    }

    /**
     * Remove devices
     *
     * @param \MongoBundle\Document\Devices $devices
     */
    public function removeDevice(\MongoBundle\Document\Devices $devices)
    {
        $this->devices->removeElement($devices);
    }

    /**
     * Get devices
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDevices()
    {
        return $this->devices;
    }

    /**
     * Add colors
     *
     * @param \MongoBundle\Document\Color $colors
     */
    public function addColor(\MongoBundle\Document\Color $colors)
    {
        $this->colors[] = $colors;
    }

    /**
     * Remove colors
     *
     * @param \MongoBundle\Document\Color $colors
     */
    public function removeColor(\MongoBundle\Document\Color $colors)
    {
        $this->colors->removeElement($colors);
    }

    /**
     * Get colors
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getColors()
    {
        return $this->colors;
    }

    /**
     * Add ressources
     *
     * @param \MongoBundle\Document\Ressources $ressources
     */
    public function addRessource(\MongoBundle\Document\Ressources $ressources)
    {
        $this->ressources[] = $ressources;
    }

    /**
     * Remove ressources
     *
     * @param \GrappboxBundle\Entity\Ressources $ressources
     */
    public function removeRessource(\MongoBundle\Document\Ressources $ressources)
    {
        $this->ressources->removeElement($ressources);
    }

    /**
     * Get ressources
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRessources()
    {
        return $this->ressources;
    }
}
