<?php

namespace MongoBundle\Document;



/**
 * MongoBundle\Document\Bug
 */
class Bug
{
    /**
     * @var $id
     */
    protected $id;

    /**
     * @var string $title
     */
    protected $title;

    /**
     * @var string $description
     */
    protected $description;

    /**
     * @var int $state
     */
    protected $state;

    /**
     * @var date $createdAt
     */
    protected $createdAt;

    /**
     * @var date $editedAt
     */
    protected $editedAt;

    /**
     * @var boolean $clientOrigin
     */
    protected $clientOrigin;

    /**
     * @var MongoBundle\Document\Project
     */
    protected $projects;

    /**
     * @var MongoBundle\Document\User
     */
    protected $creator;

    /**
     * @var MongoBundle\Document\User
     */
    protected $users = array();

    /**
     * @var MongoBundle\Document\BugtrackerTag
     */
    protected $bugtracker_tags = array();

    /**
     * @var MongoBundle\Document\BugComment
     */
    protected $comments = array();

    public function __construct()
    {
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
        $this->bugtracker_tags = new \Doctrine\Common\Collections\ArrayCollection();
        $this->comment = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get object content into array
     *
     * @return array
     */
    public function objectToArray()
    {
      $tags = array();
      $i = 0;
      foreach ($this->bugtracker_tags as $key => $value) {
          $tags[$i] = $value->objectToArray();
          $i++;
      }
      $participants = array();
      foreach ($this->users as $key => $value) {
       $participants[] = array(
           "id" => $value->getId(),
           "firstname" => $value->getFirstname(),
           "lastname" => $value->getLastname()
       );
      }
      return array(
          "id" => $this->id,
          "creator" => array("id" => $this->creator->getId(), "firstname" => $this->creator->getFirstname(), "lastname" => $this->creator->getLastname()),
          "projectId" => $this->projects->getId(),
          "title" => $this->title,
          "description" => $this->description,
          "createdAt" => $this->createdAt ? $this->createdAt->format('Y-m-d H:i:s') : null,
          "editedAt" => $this->editedAt ? $this->editedAt->format('Y-m-d H:i:s') : null,
          "clientOrigin" => $this->clientOrigin,
          "state" => $this->state,
          'tags' => $tags,
          'users' => $participants
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
     * Set title
     *
     * @param string $title
     * @return self
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Get title
     *
     * @return string $title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get description
     *
     * @return string $description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set state
     *
     * @param int $state
     * @return self
     */
    public function setState($state)
    {
        $this->state = $state;
        return $this;
    }

    /**
     * Get state
     *
     * @return int $state
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set createdAt
     *
     * @param date $createdAt
     * @return self
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * Get createdAt
     *
     * @return date $createdAt
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set editedAt
     *
     * @param date $editedAt
     * @return self
     */
    public function setEditedAt($editedAt)
    {
        $this->editedAt = $editedAt;
        return $this;
    }

    /**
     * Get editedAt
     *
     * @return date $editedAt
     */
    public function getEditedAt()
    {
        return $this->editedAt;
    }

    /**
     * Set clientOrigin
     *
     * @param boolean $clientOrigin
     * @return self
     */
    public function setClientOrigin($clientOrigin)
    {
        $this->clientOrigin = $clientOrigin;
        return $this;
    }

    /**
     * Get clientOrigin
     *
     * @return date $clientOrigin
     */
    public function getClientOrigin()
    {
        return $this->clientOrigin;
    }

    /**
     * Set projects
     *
     * @param MongoBundle\Document\Project $projects
     * @return self
     */
    public function setProjects( $projects)
    {
        $this->projects = $projects;
        return $this;
    }

    /**
     * Get projects
     *
     * @return MongoBundle\Document\Project $projects
     */
    public function getProjects()
    {
        return $this->projects;
    }

    /**
     * Set creator
     *
     * @param MongoBundle\Document\User $creator
     * @return self
     */
    public function setCreator( $creator)
    {
        $this->creator = $creator;
        return $this;
    }

    /**
     * Get creator
     *
     * @return MongoBundle\Document\User $creator
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * Add user
     *
     * @param MongoBundle\Document\User $user
     * @return self
     */
    public function addUser($user)
    {
        $this->users[] = $user;
        return $this;
    }

    /**
     * Remove user
     *
     * @param MongoBundle\Document\User $user
     */
    public function removeUser( $user)
    {
        $this->users->removeElement($user);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection $users
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Add tag
     *
     * @param MongoBundle\Document\Tag $tag
     * @return self
     */
    public function addTag( $tag)
    {
        $this->tags[] = $tag;
        return $this;
    }

    /**
     * Remove tag
     *
     * @param MongoBundle\Document\Tag $tag
     */
    public function removeTag( $tag)
    {
        $this->tags->removeElement($tag);
    }

    /**
     * Get tags
     *
     * @return \Doctrine\Common\Collections\Collection $tags
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Add bugtracker_tags
     *
     * @param MongoBundle\Document\BugtrackerTag $bugtrackerTags
     * @return self
     */
    public function addBugtrackerTag( $bugtrackerTags)
    {
        $this->bugtracker_tags[] = $bugtrackerTags;
        return $this;
    }

    /**
     * Remove bugtracker_tags
     *
     * @param MongoBundle\Document\BugtrackerTag $bugtrackerTags
     */
    public function removeBugtrackerTag( $bugtrackerTags)
    {
        $this->bugtracker_tags->removeElement($bugtrackerTags);
    }

    /**
     * Get bugtracker_tags
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBugtrackerTags()
    {
        return $this->bugtracker_tags;
    }

    /**
     * Add comments
     *
     * @param MongoBundle\Document\BugComment $comment
     * @return self
     */
    public function addComment( $comment)
    {
        $this->comments[] = $comment;
        return $this;
    }

    /**
     * Remove comments
     *
     * @param MongoBundle\Document\BugComment $comment
     */
    public function removeComment( $comment)
    {
        $this->comments->removeElement($comment);
    }

    /**
     * Get comments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getComments()
    {
        return $this->comments;
    }

}
