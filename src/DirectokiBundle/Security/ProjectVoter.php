<?php

namespace DirectokiBundle\Security;

use DirectokiBundle\Entity\Project;
use JMBTechnology\UserAccountsBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class ProjectVoter extends Voter {
    const ADMIN = 'admin';


    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, array(self::ADMIN))) {
            return false;
        }

        // only vote on Post objects inside this voter
        if (!$subject instanceof Project) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        return $this->getVoteOnProjectForAttributeForUser($subject, $attribute, ($token->getUser() instanceof User ?  $token->getUser() : null));
    }


    public function getVoteOnProjectForAttributeForUser(Project $subject, $attribute, User $user=null)
    {

        $doctrine = $this->container->get('doctrine')->getManager();

        switch ($attribute) {
            case self::ADMIN:
                // Anonymous users def can't
                if (!($user instanceof User)) {
                    return false;
                }

                // Owner definitely can
                if ($user == $subject->getOwner()) {
                    return true;
                }

                // ProjectHasAdmin
                if($doctrine->getRepository('DirectokiBundle:ProjectAdmin')->findOneBy(array('project'=>$subject, 'user'=>$user))) {
                    return true;
                }

                // Others can not.
                return false;
        }

        throw new \LogicException('This code should not be reached!');
    }



}

