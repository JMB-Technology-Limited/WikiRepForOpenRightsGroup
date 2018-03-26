<?php

namespace DirectokiBundle\Entity;



use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 * @ORM\Entity(repositoryClass="DirectokiBundle\Repository\SelectValueRepository")
 * @ORM\Table(name="directoki_select_value", uniqueConstraints={@ORM\UniqueConstraint(name="select_value_public_id", columns={"field_id", "public_id"})})
 * @ORM\HasLifecycleCallbacks
 */
class SelectValue
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
     * @ORM\Column(name="public_id", type="string", length=250, unique=false, nullable=false)
     * // We don't do Assert\NotBlank() - it causes problems with forms, the pre-persist listener and DB will catch it anyway
     */
    protected $publicId;

    /**
     * @ORM\Column(name="cached_titles", type="json_array", nullable=true)
     */
    protected $cachedTitles;

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
        if ($creationEvent && !$this->createdAt) {
            $this->createdAt = new \DateTime();
        }
    }

    /**
     * @return mixed
     */
    public function getField() {
        return $this->field;
    }

    /**
     * @param mixed $field
     */
    public function setField( $field ) {
        $this->field = $field;
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
    public function getPublicId() {
        return $this->publicId;
    }

    /**
     * @param mixed $publicId
     */
    public function setPublicId( $publicId ) {
        $this->publicId = $publicId;
    }

    /**
     * @return string
     */
    public function getCachedTitles()
    {
        return $this->cachedTitles;
    }


    public function getCachedTitleForLocale(Locale $locale) {
        if (is_array($this->cachedTitles) && isset($this->cachedTitles[$locale->getId()])) {
            return $this->cachedTitles[$locale->getId()];
        }
    }

    /**
     * @param string $cachedTitles
     */
    public function setCachedTitles($cachedTitles)
    {
        $this->cachedTitles = $cachedTitles;
    }

    /**
     * @ORM\PrePersist()
     */
    public function beforeFirstSave() {
        $this->createdAt = new \DateTime("", new \DateTimeZone("UTC"));
    }


}

