<?php

namespace DirectokiBundle\Entity;



use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
abstract class BaseRecordHasFieldValue
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="DirectokiBundle\Entity\Field")
     * @ORM\JoinColumn(name="field_id", referencedColumnName="id", nullable=false)
     */
    protected $field;

    /**
     * @ORM\ManyToOne(targetEntity="DirectokiBundle\Entity\Record")
     * @ORM\JoinColumn(name="record_id", referencedColumnName="id", nullable=false)
     */
    protected $record;


    /**
     * @var \DateTime $createdAt
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    protected $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="DirectokiBundle\Entity\Event")
     * @ORM\JoinColumn(name="creation_event_id", referencedColumnName="id", nullable=false)
     */
    protected $creationEvent;


    /**
     * @var \DateTime $approvedAt
     *
     * @ORM\Column(name="approved_at", type="datetime", nullable=true)
     */
    protected $approvedAt;


    /**
     * @ORM\ManyToOne(targetEntity="DirectokiBundle\Entity\Event")
     * @ORM\JoinColumn(name="approval_event_id", referencedColumnName="id", nullable=true)
     */
    protected $approvalEvent;


    /**
     * @var \DateTime $createdAt
     *
     * @ORM\Column(name="refused_at", type="datetime", nullable=true)
     */
    protected $refusedAt;


    /**
     * @ORM\ManyToOne(targetEntity="DirectokiBundle\Entity\Event")
     * @ORM\JoinColumn(name="refusal_event_id", referencedColumnName="id", nullable=true)
     */
    protected $refusalEvent;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param mixed $field
     */
    public function setField($field)
    {
        $this->field = $field;
    }

    /**
     * @return mixed
     */
    public function getRecord() {
        return $this->record;
    }

    /**
     * @param mixed $record
     */
    public function setRecord( $record ) {
        $this->record = $record;
    }


    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getApprovedAt()
    {
        return $this->approvedAt;
    }

    /**
     * @param \DateTime $approvedAt
     */
    public function setApprovedAt($approvedAt)
    {
        $this->approvedAt = $approvedAt;
    }

    /**
     * @return \DateTime
     */
    public function getRefusedAt()
    {
        return $this->refusedAt;
    }

    /**
     * @param \DateTime $refusedAt
     */
    public function setRefusedAt($refusedAt)
    {
        $this->refusedAt = $refusedAt;
    }

    /**
     * @return mixed
     */
    public function getCreationEvent() {
        return $this->creationEvent;
    }

    /**
     * @param mixed $creationEvent
     */
    public function setCreationEvent( $creationEvent ) {
        $this->creationEvent = $creationEvent;
    }

    /**
     * @return mixed
     */
    public function getApprovalEvent() {
        return $this->approvalEvent;
    }

    /**
     * @param Event $approvalEvent
     */
    public function setApprovalEvent( Event $approvalEvent = null ) {
        $this->approvalEvent = $approvalEvent;
        if ($approvalEvent && !$this->approvedAt) {
            $this->approvedAt = new \DateTime();
        }
    }

    /**
     * @return mixed
     */
    public function getRefusalEvent() {
        return $this->refusalEvent;
    }

    /**
     * @param Event $refusalEvent
     */
    public function setRefusalEvent( Event $refusalEvent = null ) {
        $this->refusalEvent = $refusalEvent;
        if ($refusalEvent && !$this->refusedAt) {
            $this->refusedAt = new \DateTime();
        }
    }


    /**
     * @ORM\PrePersist()
     */
    public function beforeFirstSave() {
        $this->createdAt = new \DateTime("", new \DateTimeZone("UTC"));
    }





}

