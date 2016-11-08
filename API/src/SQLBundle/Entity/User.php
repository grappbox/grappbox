<?php

namespace SQLBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * User
 */
class User implements UserInterface
{
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
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $firstname;

    /**
     * @var string
     */
    private $lastname;

    /**
     * @var \DateTime
     */
    private $birthday;

    /**
     * @var string
     */
    private $avatar;

    /**
     * @var \DateTime
     */
    private $avatarDate;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $phone;

    /**
     * @var string
     */
    private $country;

    /**
     * @var string
     */
    private $linkedin;

    /**
     * @var string
     */
    private $viadeo;

    /**
     * @var string
     */
    private $twitter;

    /**
     * @var boolean
     */
    private $isClient;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $bug_creator;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $message_creator;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $event_creator;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $project_creator;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $whiteboard_creator;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $whiteboard_updator;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $whiteboard_user;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $gantt_updator;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $notifications;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $devices;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $task_creator;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $colors;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $ressources;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $authentications;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $projects;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $events;

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
     * Set firstname
     *
     * @param string $firstname
     * @return User
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get firstname
     *
     * @return string 
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     * @return User
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get lastname
     *
     * @return string 
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set birthday
     *
     * @param \DateTime $birthday
     * @return User
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;

        return $this;
    }

    /**
     * Get birthday
     *
     * @return \DateTime 
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * Set avatar
     *
     * @param string $avatar
     * @return User
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * Get avatar
     *
     * @return string 
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * Set avatarDate
     *
     * @param \DateTime $avatarDate
     * @return User
     */
    public function setAvatarDate($avatarDate)
    {
        $this->avatarDate = $avatarDate;

        return $this;
    }

    /**
     * Get avatarDate
     *
     * @return \DateTime 
     */
    public function getAvatarDate()
    {
        return $this->avatarDate;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return User
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string 
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set country
     *
     * @param string $country
     * @return User
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return string 
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set linkedin
     *
     * @param string $linkedin
     * @return User
     */
    public function setLinkedin($linkedin)
    {
        $this->linkedin = $linkedin;

        return $this;
    }

    /**
     * Get linkedin
     *
     * @return string 
     */
    public function getLinkedin()
    {
        return $this->linkedin;
    }

    /**
     * Set viadeo
     *
     * @param string $viadeo
     * @return User
     */
    public function setViadeo($viadeo)
    {
        $this->viadeo = $viadeo;

        return $this;
    }

    /**
     * Get viadeo
     *
     * @return string 
     */
    public function getViadeo()
    {
        return $this->viadeo;
    }

    /**
     * Set twitter
     *
     * @param string $twitter
     * @return User
     */
    public function setTwitter($twitter)
    {
        $this->twitter = $twitter;

        return $this;
    }

    /**
     * Get twitter
     *
     * @return string 
     */
    public function getTwitter()
    {
        return $this->twitter;
    }

    /**
     * Set isClient
     *
     * @param boolean $isClient
     * @return User
     */
    public function setIsClient($isClient)
    {
        $this->isClient = $isClient;

        return $this;
    }

    /**
     * Get isClient
     *
     * @return boolean 
     */
    public function getIsClient()
    {
        return $this->isClient;
    }

    /**
     * Add bug_creator
     *
     * @param \SQLBundle\Entity\Bug $bugCreator
     * @return User
     */
    public function addBugCreator(\SQLBundle\Entity\Bug $bugCreator)
    {
        $this->bug_creator[] = $bugCreator;

        return $this;
    }

    /**
     * Remove bug_creator
     *
     * @param \SQLBundle\Entity\Bug $bugCreator
     */
    public function removeBugCreator(\SQLBundle\Entity\Bug $bugCreator)
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
     * @param \SQLBundle\Entity\TimelineMessage $messageCreator
     * @return User
     */
    public function addMessageCreator(\SQLBundle\Entity\TimelineMessage $messageCreator)
    {
        $this->message_creator[] = $messageCreator;

        return $this;
    }

    /**
     * Remove message_creator
     *
     * @param \SQLBundle\Entity\TimelineMessage $messageCreator
     */
    public function removeMessageCreator(\SQLBundle\Entity\TimelineMessage $messageCreator)
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
     * @param \SQLBundle\Entity\Event $eventCreator
     * @return User
     */
    public function addEventCreator(\SQLBundle\Entity\Event $eventCreator)
    {
        $this->event_creator[] = $eventCreator;

        return $this;
    }

    /**
     * Remove event_creator
     *
     * @param \SQLBundle\Entity\Event $eventCreator
     */
    public function removeEventCreator(\SQLBundle\Entity\Event $eventCreator)
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
     * @param \SQLBundle\Entity\Project $projectCreator
     * @return User
     */
    public function addProjectCreator(\SQLBundle\Entity\Project $projectCreator)
    {
        $this->project_creator[] = $projectCreator;

        return $this;
    }

    /**
     * Remove project_creator
     *
     * @param \SQLBundle\Entity\Project $projectCreator
     */
    public function removeProjectCreator(\SQLBundle\Entity\Project $projectCreator)
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
     * @param \SQLBundle\Entity\Whiteboard $whiteboardCreator
     * @return User
     */
    public function addWhiteboardCreator(\SQLBundle\Entity\Whiteboard $whiteboardCreator)
    {
        $this->whiteboard_creator[] = $whiteboardCreator;

        return $this;
    }

    /**
     * Remove whiteboard_creator
     *
     * @param \SQLBundle\Entity\Whiteboard $whiteboardCreator
     */
    public function removeWhiteboardCreator(\SQLBundle\Entity\Whiteboard $whiteboardCreator)
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
     * @param \SQLBundle\Entity\Whiteboard $whiteboardUpdator
     * @return User
     */
    public function addWhiteboardUpdator(\SQLBundle\Entity\Whiteboard $whiteboardUpdator)
    {
        $this->whiteboard_updator[] = $whiteboardUpdator;

        return $this;
    }

    /**
     * Remove whiteboard_updator
     *
     * @param \SQLBundle\Entity\Whiteboard $whiteboardUpdator
     */
    public function removeWhiteboardUpdator(\SQLBundle\Entity\Whiteboard $whiteboardUpdator)
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
     * @param \SQLBundle\Entity\WhiteboardPerson $whiteboardUser
     * @return User
     */
    public function addWhiteboardUser(\SQLBundle\Entity\WhiteboardPerson $whiteboardUser)
    {
        $this->whiteboard_user[] = $whiteboardUser;

        return $this;
    }

    /**
     * Remove whiteboard_user
     *
     * @param \SQLBundle\Entity\WhiteboardPerson $whiteboardUser
     */
    public function removeWhiteboardUser(\SQLBundle\Entity\WhiteboardPerson $whiteboardUser)
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
     * @param \SQLBundle\Entity\Gantt $ganttUpdator
     * @return User
     */
    public function addGanttUpdator(\SQLBundle\Entity\Gantt $ganttUpdator)
    {
        $this->gantt_updator[] = $ganttUpdator;

        return $this;
    }

    /**
     * Remove gantt_updator
     *
     * @param \SQLBundle\Entity\Gantt $ganttUpdator
     */
    public function removeGanttUpdator(\SQLBundle\Entity\Gantt $ganttUpdator)
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
     * @param \SQLBundle\Entity\Notification $notifications
     * @return User
     */
    public function addNotification(\SQLBundle\Entity\Notification $notifications)
    {
        $this->notifications[] = $notifications;

        return $this;
    }

    /**
     * Remove notifications
     *
     * @param \SQLBundle\Entity\Notification $notifications
     */
    public function removeNotification(\SQLBundle\Entity\Notification $notifications)
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
     * @param \SQLBundle\Entity\Devices $devices
     * @return User
     */
    public function addDevice(\SQLBundle\Entity\Devices $devices)
    {
        $this->devices[] = $devices;

        return $this;
    }

    /**
     * Remove devices
     *
     * @param \SQLBundle\Entity\Devices $devices
     */
    public function removeDevice(\SQLBundle\Entity\Devices $devices)
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
     * @param \SQLBundle\Entity\Task $taskCreator
     * @return User
     */
    public function addTaskCreator(\SQLBundle\Entity\Task $taskCreator)
    {
        $this->task_creator[] = $taskCreator;

        return $this;
    }

    /**
     * Remove task_creator
     *
     * @param \SQLBundle\Entity\Task $taskCreator
     */
    public function removeTaskCreator(\SQLBundle\Entity\Task $taskCreator)
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
     * @param \SQLBundle\Entity\Color $colors
     * @return User
     */
    public function addColor(\SQLBundle\Entity\Color $colors)
    {
        $this->colors[] = $colors;

        return $this;
    }

    /**
     * Remove colors
     *
     * @param \SQLBundle\Entity\Color $colors
     */
    public function removeColor(\SQLBundle\Entity\Color $colors)
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
     * @param \SQLBundle\Entity\Ressources $ressources
     * @return User
     */
    public function addRessource(\SQLBundle\Entity\Ressources $ressources)
    {
        $this->ressources[] = $ressources;

        return $this;
    }

    /**
     * Remove ressources
     *
     * @param \SQLBundle\Entity\Ressources $ressources
     */
    public function removeRessource(\SQLBundle\Entity\Ressources $ressources)
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
     * @param \SQLBundle\Entity\Authentication $authentications
     * @return User
     */
    public function addAuthentication(\SQLBundle\Entity\Authentication $authentications)
    {
        $this->authentications[] = $authentications;

        return $this;
    }

    /**
     * Remove authentications
     *
     * @param \SQLBundle\Entity\Authentication $authentications
     */
    public function removeAuthentication(\SQLBundle\Entity\Authentication $authentications)
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
     * Add projects
     *
     * @param \SQLBundle\Entity\Project $projects
     * @return User
     */
    public function addProject(\SQLBundle\Entity\Project $projects)
    {
        $this->projects[] = $projects;

        return $this;
    }

    /**
     * Remove projects
     *
     * @param \SQLBundle\Entity\Project $projects
     */
    public function removeProject(\SQLBundle\Entity\Project $projects)
    {
        $this->projects->removeElement($projects);
    }

    /**
     * Get projects
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProjects()
    {
        return $this->projects;
    }

    /**
     * Add events
     *
     * @param \SQLBundle\Entity\Event $events
     * @return User
     */
    public function addEvent(\SQLBundle\Entity\Event $events)
    {
        $this->events[] = $events;

        return $this;
    }

    /**
     * Remove events
     *
     * @param \SQLBundle\Entity\Event $events
     */
    public function removeEvent(\SQLBundle\Entity\Event $events)
    {
        $this->events->removeElement($events);
    }

    /**
     * Get events
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getEvents()
    {
        return $this->events;
    }
}
