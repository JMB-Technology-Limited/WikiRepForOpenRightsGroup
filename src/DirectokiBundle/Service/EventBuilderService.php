<?php

namespace DirectokiBundle\Service;

use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\Project;
use JMBTechnology\UserAccountsBundle\Entity\User;
use DirectokiBundle\FieldType\FieldTypeBoolean;
use DirectokiBundle\FieldType\FieldTypeLatLng;
use DirectokiBundle\FieldType\FieldTypeString;
use DirectokiBundle\FieldType\FieldTypeText;
use Symfony\Component\HttpFoundation\Request;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class EventBuilderService
{

    protected $container;

    /**
     * @param $container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     *
     * This returns an unsaved entity only, no database writes should be done.
     * This is because we aren't certain that the event will be used yet - it might not be.
     *
     * @param Project $project
     * @param User $user
     * @param Request $request
     * @param null $comment
     *
     * @return Event
     */
    public function build(Project $project, User $user = null, Request $request = null, string $comment = null) {
        $event = new Event();
        $event->setProject($project);
        $event->setUser($user);
        $event->setComment($comment);
        if ($request) {
            if (!$this->container->hasParameter('directoki.collect_ip') || $this->container->getParameter('directoki.collect_ip')) {
                $event->setIP($request->getClientIp());
            }
            if (!$this->container->hasParameter('directoki.collect_user_agent') || $this->container->getParameter('directoki.collect_user_agent')) {
                $event->setUserAgent($request->headers->get('User-Agent'));
            }
        }
        return $event;
    }


}