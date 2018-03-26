<?php

namespace DirectokiBundle\Entity;



use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 * @ORM\Entity()
 * @ORM\Table(name="directoki_record_report")
 * @ORM\HasLifecycleCallbacks
 */
class RecordReport
{

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
     * @ORM\Column(name="description", type="text", nullable=false)
     */
    protected $description;

    
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
     * @var \DateTime $resolvedAt
     *
     * @ORM\Column(name="resolved_at", type="datetime", nullable=true)
     */
    protected $resolvedAt;

    /**
     * @ORM\ManyToOne(targetEntity="DirectokiBundle\Entity\Event")
     * @ORM\JoinColumn(name="resolution_event_id", referencedColumnName="id", nullable=true)
     */
    protected $resolutionEvent;

    /**
     * @ORM\ManyToOne(targetEntity="DirectokiBundle\Entity\ExternalCheck")
     * @ORM\JoinColumn(name="external_check_id", referencedColumnName="id", nullable=true)
     */
    protected $externalCheck;


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
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getRecord()
    {
        return $this->record;
    }

    /**
     * @param mixed $record
     */
    public function setRecord($record)
    {
        $this->record = $record;
    }

    /**
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string  $description ) {
        $this->description = $description;
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
    public function getResolvedAt()
    {
        return $this->resolvedAt;
    }

    /**
     * @param \DateTime $resolvedAt
     */
    public function setResolvedAt($resolvedAt)
    {
        $this->resolvedAt = $resolvedAt;
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
    public function getResolutionEvent() {
        return $this->resolutionEvent;
    }

    /**
     * @param mixed $resolutionEvent
     */
    public function setResolutionEvent( $resolutionEvent ) {
        $this->resolutionEvent = $resolutionEvent;
    }

    /**
     * @return ExternalCheck
     */
    public function getExternalCheck()
    {
        return $this->externalCheck;
    }

    /**
     * @param ExternalCheck $externalCheck
     */
    public function setExternalCheck(ExternalCheck $externalCheck = null)
    {
        $this->externalCheck = $externalCheck;
    }





    /**
     * @ORM\PrePersist()
     */
    public function beforeFirstSave() {
        $this->createdAt = new \DateTime("", new \DateTimeZone("UTC"));
    }


}

