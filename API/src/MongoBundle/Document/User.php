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
     * @var date $avatarDate
     */
    protected $avatarDate;

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
     * @var bool
     */
    private $isClient;

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
     * @var MongoBundle\Document\Whiteboard
     */
    private $whiteboard_creator = array();

    /**
     * @var MongoBundle\Document\Whiteboard
     */
    private $whiteboard_updator = array();

    /**
     * @var MongoBundle\Document\Whiteboard
     */
    private $whiteboard_user = array();

    /**
     * @var MongoBundle\Document\Gantt
     */
    private $gantt_updator = array();

    /**
     * @var MongoBundle\Document\Notification
     */
    protected $notifications = array();

    /**
     * @var MongoBundle\Document\Devices
     */
    protected $devices = array();

    /**
     * @var MongoBundle\Document\Task
     */
    protected $task_creator  = array();

    /**
     * @var MongoBundle\Document\Color
     */
    protected $colors = array();

    /**
     * @var MongoBundle\Document\Ressources
     */
    protected $ressources = array();

    /**
     * @var MongoBundle\Document\Authentication
     */
    private $authentications = array();

    /**
     * @var MongoBundle\Document\Project
     */
    protected $projects = array();

    /**
     * @var MongoBundle\Document\Event
     */
    protected $events = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->bug_creator = new \Doctrine\Common\Collections\ArrayCollection();
        $this->message_creator = new \Doctrine\Common\Collections\ArrayCollection();
        $this->event_creator = new \Doctrine\Common\Collections\ArrayCollection();
        $this->project_creator = new \Doctrine\Common\Collections\ArrayCollection();
        $this->whiteboard_creator = new \Doctrine\Common\Collections\ArrayCollection();
        $this->whiteboard_updator = new \Doctrine\Common\Collections\ArrayCollection();
        $this->whiteboard_user = new \Doctrine\Common\Collections\ArrayCollection();
        $this->gantt_updator = new \Doctrine\Common\Collections\ArrayCollection();
        $this->notifications = new \Doctrine\Common\Collections\ArrayCollection();
        $this->devices = new \Doctrine\Common\Collections\ArrayCollection();
        $this->task_creator = new \Doctrine\Common\Collections\ArrayCollection();
        $this->colors = new \Doctrine\Common\Collections\ArrayCollection();
        $this->ressources = new \Doctrine\Common\Collections\ArrayCollection();
        $this->authentications = new \Doctrine\Common\Collections\ArrayCollection();
        $this->projects = new \Doctrine\Common\Collections\ArrayCollection();
        $this->events = new \Doctrine\Common\Collections\ArrayCollection();
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

    public function fullObjectToArray() {
        return array(
            'id' => $this->id,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'birthday' => $this->birthday ? $this->birthday->format('Y-m-d') : null,
            'avatar' => $this->avatarDate ? $this->avatarDate->format('Y-m-d H:i:s') : null,
            'email' => $this->email,
            'phone' => $this->phone,
            'country' => $this->country,
            'linkedin' => $this->linkedin,
            'viadeo' => $this->viadeo,
            'twitter' => $this->twitter,
            'is_client' => $this->isClient
        );
    }

    public function objectToArray()
    {
        return array(
            'id' => $this->id,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'email' => $this->email,
            'avatar' => $this->avatarDate ? $this->avatarDate->format('Y-m-d H:i:s') : null,
            'is_client' => $this->isClient
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
     * Set avatarDate
     *
     * @param date $avatarDate
     * @return self
     */
    public function setAvatarDate($avatarDate)
    {
        $this->avatarDate = $avatarDate;
        return $this;
    }

    /**
     * Get avatarDate
     *
     * @return date $avatarDate
     */
    public function getAvatarDate()
    {
        return $this->avatarDate;
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
     * Set isClient
     *
     * @param bool $isClient
     * @return self
     */
    public function setIsClient($isClient)
    {
        $this->isClient = $isClient;

        return $this;
    }

    /**
     * Get isClient
     *
     * @return bool
     */
    public function getIsClient()
    {
        return $this->isClient;
    }

    /**
     * Add bug_creator
     *
     * @param MongoBundle\Document\Bug $bugCreator
     * @return self
     */
    public function addBugCreator( $bugCreator)
    {
        $this->bug_creator[] = $bugCreator;

        return $this;
    }

    /**
     * Remove bug_creator
     *
     * @param MongoBundle\Document\Bug $bugCreator
     */
    public function removeBugCreator( $bugCreator)
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
     * @param MongoBundle\Document\TimelineMessage $messageCreator
     * @return self
     */
    public function addMessageCreator( $messageCreator)
    {
        $this->message_creator[] = $messageCreator;

        return $this;
    }

    /**
     * Remove message_creator
     *
     * @param MongoBundle\Document\TimelineMessage $messageCreator
     */
    public function removeMessageCreator( $messageCreator)
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
     * Add event_creator
     *
     * @param MongoBundle\Document\Event $eventCreator
     * @return self
     */
    public function addEventCreator( $eventCreator)
    {
        $this->event_creator[] = $eventCreator;

        return $this;
    }

    /**
     * Remove event_creator
     *
     * @param MongoBundle\Document\Event $eventCreator
     */
    public function removeEventCreator( $eventCreator)
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
     * Add project_creator
     *
     * @param MongoBundle\Document\Project $projectCreator
     * @return self
     */
    public function addProjectCreator( $projectCreator)
    {
        $this->project_creator[] = $projectCreator;

        return $this;
    }

    /**
     * Remove project_creator
     *
     * @param MongoBundle\Document\Project $projectCreator
     */
    public function removeProjectCreator( $projectCreator)
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
     * Add whiteboard_creator
     *
     * @param MongoBundle\Document\Whiteboard $whiteboardCreator
     * @return User
     */
    public function addWhiteboardCreator($whiteboardCreator)
    {
        $this->whiteboard_creator[] = $whiteboardCreator;

        return $this;
    }

    /**
     * Remove whiteboard_creator
     *
     * @param MongoBundle\Document\Whiteboard $whiteboardCreator
     */
    public function removeWhiteboardCreator($whiteboardCreator)
    {
        $this->whiteboard_creator->removeElement($whiteboardCreator);
    }

    /**
     * Get whiteboard_creator
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getWhiteboardCreator()
    {
        return $this->whiteboard_creator;
    }

    /**
     * Add whiteboard_updator
     *
     * @param MongoBundle\Document\Whiteboard $whiteboardUpdator
     * @return User
     */
    public function addWhiteboardUpdator($whiteboardUpdator)
    {
        $this->whiteboard_updator[] = $whiteboardUpdator;

        return $this;
    }

    /**
     * Remove whiteboard_updator
     *
     * @param MongoBundle\Document\Whiteboard $whiteboardUpdator
     */
    public function removeWhiteboardUpdator($whiteboardUpdator)
    {
        $this->whiteboard_updator->removeElement($whiteboardUpdator);
    }

    /**
     * Get whiteboard_updator
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getWhiteboardUpdator()
    {
        return $this->whiteboard_updator;
    }

    /**
     * Add whiteboard_user
     *
     * @param MongoBundle\Document\WhiteboardPerson $whiteboardUser
     * @return User
     */
    public function addWhiteboardUser($whiteboardUser)
    {
        $this->whiteboard_user[] = $whiteboardUser;

        return $this;
    }

    /**
     * Remove whiteboard_user
     *
     * @param MongoBundle\Document\WhiteboardPerson $whiteboardUser
     */
    public function removeWhiteboardUser($whiteboardUser)
    {
        $this->whiteboard_user->removeElement($whiteboardUser);
    }

    /**
     * Get whiteboard_user
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getWhiteboardUser()
    {
        return $this->whiteboard_user;
    }

    /**
     * Add gantt_updator
     *
     * @param MongoBundle\Document\Gantt $ganttCreator
     * @return self
     */
    public function addGanttUpdator( $ganttUpdator)
    {
        $this->gantt_updator[] = $ganttUpdator;

        return $this;
    }

    /**
     * Remove gantt_updator
     *
     * @param MongoBundle\Document\Gantt $ganttCreator
     */
    public function removeGanttUpdator( $ganttUpdator)
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
     * @param MongoBundle\Document\Notification $notifications
     * @return self
     */
    public function addNotification( $notifications)
    {
        $this->notifications[] = $notifications;

        return $this;
    }

    /**
     * Remove notifications
     *
     * @param MongoBundle\Document\Notification $notifications
     */
    public function removeNotification( $notifications)
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
     * Add devices
     *
     * @param MongoBundle\Document\Devices $devices
     * @return self
     */
    public function addDevice( $devices)
    {
        $this->devices[] = $devices;

        return $this;
    }

    /**
     * Remove devices
     *
     * @param MongoBundle\Document\Devices $devices
     */
    public function removeDevice( $devices)
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
     * Add task_creator
     *
     * @param MongoBundle\Document\Task $taskCreator
     * @return User
     */
    public function addTaskCreator($taskCreator)
    {
        $this->task_creator[] = $taskCreator;

        return $this;
    }

    /**
     * Remove task_creator
     *
     * @param MongoBundle\Document\Task $taskCreator
     */
    public function removeTaskCreator($taskCreator)
    {
        $this->task_creator->removeElement($taskCreator);
    }

    /**
     * Get task_creator
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTaskCreator()
    {
        return $this->task_creator;
    }

    /**
     * Add colors
     *
     * @param MongoBundle\Document\Color $colors
     * @return self
     */
    public function addColor( $colors)
    {
        $this->colors[] = $colors;

        return $this;
    }

    /**
     * Remove colors
     *
     * @param MongoBundle\Document\Color $colors
     */
    public function removeColor( $colors)
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
     * @param MongoBundle\Document\Ressources $ressources
     * @return self
     */
    public function addRessource( $ressources)
    {
        $this->ressources[] = $ressources;

        return $this;
    }

    /**
     * Remove ressources
     *
     * @param MongoBundle\Document\Ressources $ressources
     */
    public function removeRessource( $ressources)
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

    /**
     * Add authentications
     *
     * @param MongoBundle\Document\Authentication $authentications
     * @return User
     */
    public function addAuthentication($authentications)
    {
        $this->authentications[] = $authentications;

        return $this;
    }

    /**
     * Remove authentications
     *
     * @param MongoBundle\Document\Authentication $authentications
     */
    public function removeAuthentication($authentications)
    {
        $this->authentications->removeElement($authentications);
    }

    /**
     * Get authentications
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAuthentications()
    {
        return $this->authentications;
    }

    /**
     * Add project
     *
     * @param MongoBundle\Document\Project $project
     * @return self
     */
    public function addProject( $project)
    {
        $this->projects[] = $project;

        return $this;
    }

    /**
     * Remove project
     *
     * @param MongoBundle\Document\Project $project
     */
    public function removeProject( $project)
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
     * @return self
     */
    public function addEvent( $event)
    {
        $this->events[] = $event;

        return $this;
    }

    /**
     * Remove event
     *
     * @param MongoBundle\Document\Event $event
     */
    public function removeEvent( $event)
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
}
