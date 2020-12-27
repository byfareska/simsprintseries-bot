<?php declare(strict_types=1);

namespace App\Discord\Utility\Plugin;

use Discord\Discord;
use Discord\Parts\Part;
use Discord\Parts\User\Member;
use Discord\Parts\User\User;

final class PermissionsChecker
{
    private Discord $discord;

    public function __construct(Discord $discord)
    {
        $this->discord = $discord;
    }

    /**
     * @param User|Member $user
     * @param callable $onSuccess
     * @param callable $onFail
     */
    public function executeIfAdmin(Part $user, callable $onSuccess, callable $onFail): void
    {
        $this->executeIfUserHasOneOfRoles($user, explode(",", $_ENV['ADMIN_GROUPS']), $onSuccess, $onFail);
    }

    /**
     * @param User|Member $user
     * @param array $roles
     * @param callable $onSuccess
     * @param callable $onFail
     */
    public function executeIfUserHasOneOfRoles(Part $user, array $roles, callable $onSuccess, callable $onFail): void
    {
        $onMemberFetch = fn(Member $member) => $this->memberHasOneOfRole($member, $roles) ? $onSuccess() : $onFail();
        $guild = $this->discord->guilds->get('id', (string)$_ENV['DISCORD_GUILD']);
        $guild->members->fetch($user->id)->done($onMemberFetch);
    }

    public function memberHasOneOfRole(Member $member, array $roles): bool
    {
        foreach ($member->roles as $role) {
            if (in_array($role->id, $roles))
                return true;
        }

        return false;
    }
}