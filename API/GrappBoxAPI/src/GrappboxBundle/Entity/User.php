<?php

namespace GrappboxBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * User
 */
class User implements UserInterface
{
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
     * @var string
     */
    private $token;

    /**
     * @var \DateTime
     */
    private $tokenValidity;

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
    private $gantt_creator;

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
    private $projects;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $events;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $tasks;

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
     * Set token
     *
     * @param string $token
     * @return User
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
     * @return User
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
     * Add event_creator
     *
     * @param \GrappboxBundle\Entity\Event $eventCreator
     * @return User
     */
    public function addEventCreator(\GrappboxBundle\Entity\Event $eventCreator)
    {
        $this->event_creator[] = $eventCreator;

        return $this;
    }

    /**
     * Remove event_creator
     *
     * @param \GrappboxBundle\Entity\Event $eventCreator
     */
    public function removeEventCreator(\GrappboxBundle\Entity\Event $eventCreator)
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
     * @param \GrappboxBundle\Entity\Bug $bugCreator
     * @return User
     */
    public function addBugCreator(\GrappboxBundle\Entity\Bug $bugCreator)
    {
        $this->bug_creator[] = $bugCreator;

        return $this;
    }

    /**
     * Remove bug_creator
     *
     * @param \GrappboxBundle\Entity\Bug $bugCreator
     */
    public function removeBugCreator(\GrappboxBundle\Entity\Bug $bugCreator)
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
     * @param \GrappboxBundle\Entity\TimelineMessage $messageCreator
     * @return User
     */
    public function addMessageCreator(\GrappboxBundle\Entity\TimelineMessage $messageCreator)
    {
        $this->message_creator[] = $messageCreator;

        return $this;
    }

    /**
     * Remove message_creator
     * @param \GrappboxBundle\Entity\TimelineMessage $messageCreator
     */
    public function removeMessageCreator(\GrappboxBundle\Entity\TimelineMessage $messageCreator)
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
     * @param \GrappboxBundle\Entity\Project $projectCreator
     * @return User
     */
    public function addProjectCreator(\GrappboxBundle\Entity\Project $projectCreator)
    {
        $this->project_creator[] = $projectCreator;

        return $this;
    }

    /**
     * Remove project_creator
     *
     * @param \GrappboxBundle\Entity\Project $projectCreator
     */
    public function removeProjectCreator(\GrappboxBundle\Entity\Project $projectCreator)
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
     * @param \GrappboxBundle\Entity\Gantt $ganttCreator
     * @return User
     */
    public function addGanttCreator(\GrappboxBundle\Entity\Gantt $ganttCreator)
    {
        $this->gantt_creator[] = $ganttCreator;

        return $this;
    }

    /**
     * Remove gantt_creator
     *
     * @param \GrappboxBundle\Entity\Gantt $ganttCreator
     */
    public function removeGanttCreator(\GrappboxBundle\Entity\Gantt $ganttCreator)
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
     * @param \GrappboxBundle\Entity\Gantt $ganttUpdator
     * @return User
     */
    public function addGanttUpdator(\GrappboxBundle\Entity\Gantt $ganttUpdator)
    {
        $this->gantt_updator[] = $ganttUpdator;

        return $this;
    }

    /**
     * Remove gantt_updator
     *
     * @param \GrappboxBundle\Entity\Gantt $ganttUpdator
     */
    public function removeGanttUpdator(\GrappboxBundle\Entity\Gantt $ganttUpdator)
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
     * @param \GrappboxBundle\Entity\Notification $notifications
     * @return User
     */
    public function addNotification(\GrappboxBundle\Entity\Notification $notifications)
    {
        $this->notifications[] = $notifications;

        return $this;
    }

    /**
     * Remove notifications
     *
     * @param \GrappboxBundle\Entity\Notification $notifications
     */
    public function removeNotification(\GrappboxBundle\Entity\Notification $notifications)
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
     * Add projects
     *
     * @param \GrappboxBundle\Entity\Project $projects
     * @return User
     */
    public function addProject(\GrappboxBundle\Entity\Project $projects)
    {
        $this->projects[] = $projects;

        return $this;
    }

    /**
     * Remove projects
     *
     * @param \GrappboxBundle\Entity\Project $projects
     */
    public function removeProject(\GrappboxBundle\Entity\Project $projects)
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
     * @param \GrappboxBundle\Entity\Event $events
     * @return User
     */
    public function addEvent(\GrappboxBundle\Entity\Event $events)
    {
        $this->events[] = $events;

        return $this;
    }

    /**
     * Remove events
     *
     * @param \GrappboxBundle\Entity\Event $events
     */
    public function removeEvent(\GrappboxBundle\Entity\Event $events)
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

    /**
     * Add tasks
     *
     * @param \GrappboxBundle\Entity\Task $tasks
     * @return User
     */
    public function addTask(\GrappboxBundle\Entity\Task $tasks)
    {
        $this->tasks[] = $tasks;

        return $this;
    }

    /**
     * Remove tasks
     *
     * @param \GrappboxBundle\Entity\Task $tasks
     */
    public function removeTask(\GrappboxBundle\Entity\Task $tasks)
    {
        $this->tasks->removeElement($tasks);
    }

    /**
     * Get tasks
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTasks()
    {
        return $this->tasks;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $devices;


    /**
     * Add devices
     *
     * @param \GrappboxBundle\Entity\Devices $devices
     * @return User
     */
    public function addDevice(\GrappboxBundle\Entity\Devices $devices)
    {
        $this->devices[] = $devices;

        return $this;
    }

    /**
     * Remove devices
     *
     * @param \GrappboxBundle\Entity\Devices $devices
     */
    public function removeDevice(\GrappboxBundle\Entity\Devices $devices)
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
     * @var \Doctrine\Common\Collections\Collection
     */
    private $task_creator;


    /**
     * Add task_creator
     *
     * @param \GrappboxBundle\Entity\Task $taskCreator
     * @return User
     */
    public function addTaskCreator(\GrappboxBundle\Entity\Task $taskCreator)
    {
        $this->task_creator[] = $taskCreator;

        return $this;
    }

    /**
     * Remove task_creator
     *
     * @param \GrappboxBundle\Entity\Task $taskCreator
     */
    public function removeTaskCreator(\GrappboxBundle\Entity\Task $taskCreator)
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
     * @var \Doctrine\Common\Collections\Collection
     */
    private $colors;


    /**
     * Add colors
     *
     * @param \GrappboxBundle\Entity\Color $colors
     * @return User
     */
    public function addColor(\GrappboxBundle\Entity\Color $colors)
    {
        $this->colors[] = $colors;

        return $this;
    }

    /**
     * Remove colors
     *
     * @param \GrappboxBundle\Entity\Color $colors
     */
    public function removeColor(\GrappboxBundle\Entity\Color $colors)
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
}
