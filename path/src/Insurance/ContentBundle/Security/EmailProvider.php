<?php
# Insurance\ContentBundle\Security\EmailProvider
namespace Insurance\ContentBundle\Security;

use FOS\UserBundle\Security\UserProvider;

class EmailProvider extends UserProvider
{
    protected function findUser($username)
    {
        return $this->userManager->findUserByEmail($username);
    }
}?>
