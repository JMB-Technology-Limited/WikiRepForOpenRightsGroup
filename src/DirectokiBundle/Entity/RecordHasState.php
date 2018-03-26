<?php

namespace DirectokiBundle\Entity;



use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 * @ORM\Entity(repositoryClass="DirectokiBundle\Repository\RecordHasStateRepository")
 * @ORM\Table(name="directoki_record_has_state")
 * @ORM\HasLifecycleCallbacks
 */
class RecordHasState
{

    const STATE_DRAFT = 'draft';
    const STATE_PUBLISHED = 'published';
    const STATE_DELETED = 'deleted';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;


    /**
     * @ORM\ManyToOne(targetEntity="DirectokiBundle\Entity\Record")
     * @ORM\JoinColumn(name="record_id", referencedColumnName="id", nullable=false)
     */
    protected $record;

    /**
     * @var string
     *
     * @ORM\Column(name="state", type="text", nullable=false)
     */
    protected $state;

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
    public function getApprovalEvent() {
        return $this->approvalEvent;
    }

    /**
     * @param mixed $approvalEvent
     */
    public function setApprovalEvent( $approvalEvent ) {
        $this->approvalEvent = $approvalEvent;
        if ($approvalEvent && !$this->approvedAt) {
            $this->approvedAt = new \DateTime();
        }
    }

    /**
     * @return \DateTime
     */
    public function getApprovedAt() {
        return $this->approvedAt;
    }

    /**
     * @param \DateTime $approvedAt
     */
    public function setApprovedAt( $approvedAt ) {
        $this->approvedAt = $approvedAt;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt() {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt( $createdAt ) {
        $this->createdAt = $createdAt;
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
    public function getId() {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId( int $id ) {
        $this->id = $id;
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
     * @return mixed
     */
    public function getRefusalEvent() {
        return $this->refusalEvent;
    }

    /**
     * @param mixed $refusalEvent
     */
    public function setRefusalEvent( $refusalEvent ) {
        $this->refusalEvent = $refusalEvent;
        if ($refusalEvent && !$this->refusedAt) {
            $this->refusedAt = new \DateTime();
        }
    }

    /**
     * @return \DateTime
     */
    public function getRefusedAt() {
        return $this->refusedAt;
    }

    /**
     * @param \DateTime $refusedAt
     */
    public function setRefusedAt( $refusedAt ) {
        $this->refusedAt = $refusedAt;
    }

    /**
     * @return string
     */
    public function getState() {
        return $this->state;
    }

    /**
     * @param string $state
     */
    public function setState( $state ) {
        $this->state = $state;
    }



    public function isStateDraft() {
        return $this->state == $this::STATE_DRAFT;
    }
    public function isStatePublished() {
        return $this->state == $this::STATE_PUBLISHED;
    }
    public function isStateDeleted() {
        return $this->state == $this::STATE_DELETED;
    }




    /**
     * @ORM\PrePersist()
     */
    public function beforeFirstSave() {
        $this->createdAt = new \DateTime("", new \DateTimeZone("UTC"));
    }


}

