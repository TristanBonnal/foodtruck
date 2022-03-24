<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Vérification de l'utilisateur courant
 */
class UserVoter extends Voter
{
    protected function supports(string $attribute, $subject): bool
    {
        return $attribute == 'USER' && $subject instanceof \App\Entity\User;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $currentUser = $token->getUser();
        if (!$currentUser instanceof UserInterface) {
            return false;
        }
        switch ($attribute) {
            case 'USER':
                return $currentUser == $subject;
                break;
        }
    }
}
