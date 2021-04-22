<?php declare(strict_types=1);

namespace App\Discord\Plugin\Welcome;

use App\Discord\Plugin\AbstractPlugin;
use App\Discord\Plugin\UpcomingEvents\EmbedGenerator;
use Discord\Parts\User\Member;
use Discord\WebSockets\Event;
use Twig\Environment;

final class Welcome extends AbstractPlugin
{
    private EmbedGenerator $embedGenerator;
    private Environment $templating;

    public function __construct(EmbedGenerator $embedGenerator, Environment $templating)
    {
        $this->embedGenerator = $embedGenerator;
        $this->templating = $templating;
    }

    protected function bind(): void
    {
        $this->discord->on(Event::GUILD_MEMBER_ADD,
            fn(Member $member) => $this->memberJoinHandler($member)
        );
    }

    private function memberJoinHandler(Member $member): void
    {
        echo "join\n";
        var_dump($member->guild->id, (string)$_ENV['DISCORD_GUILD']);
        var_dump($member->guild->id !== (string)$_ENV['DISCORD_GUILD']);

        if ($member->guild->id !== (string)$_ENV['DISCORD_GUILD'])
            return;

        $this->sendMessage($member);
    }

    private function sendMessage(Member $member): void
    {
        $upcomingEventsEmbed = $this->embedGenerator->generate();

        $msg = $this->templating->render("welcome/message.twig", [
            "name" => $member->username
        ]);

        $member->user->sendMessage($msg, false, $upcomingEventsEmbed);
    }
}