<?php declare(strict_types=1);

namespace App\Discord\Plugin;

use App\Discord\Utility\Plugin\MessageMatcher;
use App\Discord\Utility\Plugin\PermissionsChecker;
use Discord\Discord;
use Discord\Parts\Part;
use Discord\Parts\User\Member;
use Discord\Parts\User\User;
use React\Promise\ExtendedPromiseInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractPlugin implements PluginInterface
{
    protected Discord $discord;
    protected InputInterface $input;
    protected OutputInterface $output;
    protected MessageMatcher $matcher;
    protected PermissionsChecker $permissions;

    public function init(Discord $discord, InputInterface $input, OutputInterface $output, PermissionsChecker $permissionsChecker)
    {
        $this->discord = $discord;
        $this->input = &$input;
        $this->output = &$output;
        $this->permissions = $permissionsChecker;
        $this->matcher = (new MessageMatcher($this->discord))
            ->setMessageEquals($this->getMessageEquals())
            ->setMessageContains($this->getMessageContains())
            ->setMessageContainsRegex($this->getMessageContainsRegex());
        $this->bind();
    }

    /**
     * @param User|Member $user
     * @return ExtendedPromiseInterface
     */
    protected function fetchMember(Part $user): ExtendedPromiseInterface
    {
        return $this->discord->guilds->get('id', (string)$_ENV['DISCORD_GUILD'])->members->fetch($user->id);
    }

    abstract protected function bind(): void;

    public function getMessageContains(): array
    {
        return [];
    }

    public function getMessageContainsRegex(): array
    {
        return [];
    }

    public function getMessageEquals(): array
    {
        return [];
    }
}