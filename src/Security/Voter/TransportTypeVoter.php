<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class TransportTypeVoter extends Voter
{
    public const DELETE = 'DELETE';
    public const UPDATE = 'UPDATE';
    public const VIEW = 'VIEW';
    public const CREATE = 'CREATE';
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::VIEW, self::UPDATE, self::DELETE, self::CREATE]);
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();


        if ($this->security->isGranted('USER_SUPER_ADMIN')) {
            return true;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::VIEW:
                return $this->canView($user);
            case self::UPDATE:
                return $this->canUpdate($user);
            case self::DELETE:
                return $this->canDelete($user);
            case self::CREATE:
                return $this->canCreate($user);
        }

        return false;
    }


    private function canView($user): bool
    {
        return true;
    }

    private function canUpdate($user): bool
    {
        return in_array('USER_ADMIN', $user->getRoles(), true);
    }

    private function canCreate($user): bool
    {
        return in_array('USER_ADMIN', $user->getRoles(), true);
    }

    private function canDelete($user): bool
    {
        return in_array('USER_ADMIN', $user->getRoles(), true);
    }
}
