<?php

namespace App\Security\Voter;

use App\Entity\Transport;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class TransportVoter extends Voter
{
    public const EDIT = 'EDIT';
    public const DELETE = 'DELETE';
    public const UPDATE = 'UPDATE';
    public const VIEW = 'VIEW';
    public const CREATE = 'CREATE';

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::EDIT, self::VIEW, self::UPDATE, self::DELETE, self::CREATE]);
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access


        if ($this->security->isGranted('USER_SUPER_ADMIN')) {
            return true;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::EDIT:
                return $this->canEdit($user);
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

    private function canEdit($user): bool
    {
        return in_array('USER_ADMIN', $user->getRoles(), true) && $user instanceof UserInterface;
    }

    private function canView($user): bool
    {
        return true;
    }

    private function canUpdate($user): bool
    {
        return in_array('USER_ADMIN', $user->getRoles(), true) && $user instanceof UserInterface;
    }

    private function canCreate($user): bool
    {
        return in_array('USER_ADMIN', $user->getRoles(), true) && $user instanceof UserInterface;
    }

    private function canDelete($user): bool
    {
        return in_array('USER_ADMIN', $user->getRoles(), true) && $user instanceof UserInterface;
    }
}
