<?php


namespace DirectokiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 * @ORM\Entity(repositoryClass="DirectokiBundle\Repository\ContactRepository")
 * @ORM\Table(name="directoki_contact", uniqueConstraints={@ORM\UniqueConstraint(name="contact_public_id", columns={"project_id", "public_id"})})
 * @ORM\HasLifecycleCallbacks
 */

class Contact {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="DirectokiBundle\Entity\Project")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id", nullable=false)
     */
    private $project;


    /**
     * @ORM\Column(name="public_id", type="string", length=250, unique=false, nullable=false)
     * @Assert\NotBlank()
     */
    private $publicId;

    /**
     * @var string
     *
     *
     * @TODO make this unique across project somehow? Allow nulls for future tho!
     * @ORM\Column(name="email", type="string", length=250, nullable=true)
     */
    private $email;

    /**
     * @var \DateTime $createdAt
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    private $createdAt;

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
     * @return string
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail( string $email ) {
        $this->email = $email;
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
    public function getProject() {
        return $this->project;
    }

    /**
     * @param mixed $project
     */
    public function setProject( $project ) {
        $this->project = $project;
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
    public function setPublicId( string  $publicId ) {
        $this->publicId = $publicId;
    }

    /**
     * @ORM\PrePersist()
     */
    public function beforeFirstSave() {
        $this->createdAt = new \DateTime("", new \DateTimeZone("UTC"));
    }

}
