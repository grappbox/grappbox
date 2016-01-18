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
     * @var MongoBundle\Document\Project
     */
    protected $projects = array();

    /**
     * @var MongoBundle\Document\Event
     */
    protected $events = array();

    /**
     * @var MongoBundle\Document\Task
     */
    protected $tasks = array();

    public function __construct()
    {
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
     * Add task
     *
     * @param MongoBundle\Document\Task $task
     */
    public function addTask(\MongoBundle\Document\Task $task)
    {
        $this->tasks[] = $task;
    }

    /**
     * Remove task
     *
     * @param MongoBundle\Document\Task $task
     */
    public function removeTask(\MongoBundle\Document\Task $task)
    {
        $this->tasks->removeElement($task);
    }

    /**
     * Get tasks
     *
     * @return \Doctrine\Common\Collections\Collection $tasks
     */
    public function getTasks()
    {
        return $this->tasks;
    }
}