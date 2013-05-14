<?php

namespace Rami\FerientabelleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Timeframe
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Timeframe
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
     * @ORM\ManyToOne(targetEntity="User", inversedBy="timeframes")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;
    
    
    /**
     * @ORM\Column(type="date", name="date_from")
     */
    protected $from;
    
    
    /**
     * @ORM\Column(type="date", name="date_to")
     */
    protected $to;
    
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $availability = 0;
    
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $comment;

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
     * Set availability
     *
     * @param integer $availability
     * @return Timeframe
     */
    public function setAvailability($availability)
    {
        $this->availability = $availability;
    
        return $this;
    }

    /**
     * Get availability
     *
     * @return integer 
     */
    public function getAvailability()
    {
        return $this->availability;
    }

    /**
     * Set comment
     *
     * @param string $comment
     * @return Timeframe
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    
        return $this;
    }

    /**
     * Get comment
     *
     * @return string 
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set user
     *
     * @param \Rami\FerientabelleBundle\Entity\User $user
     * @return Timeframe
     */
    public function setUser(\Rami\FerientabelleBundle\Entity\User $user = null)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return \Rami\FerientabelleBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set from
     *
     * @param \DateTime $from
     * @return Timeframe
     */
    public function setFrom($from)
    {
        $this->from = $from;

        return $this;
    }

    /**
     * Get from
     *
     * @return \DateTime 
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * Set to
     *
     * @param \DateTime $to
     * @return Timeframe
     */
    public function setTo($to)
    {
        $this->to = $to;

        return $this;
    }

    /**
     * Get to
     *
     * @return \DateTime 
     */
    public function getTo()
    {
        return $this->to;
    }
}
