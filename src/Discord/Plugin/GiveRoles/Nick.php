<?php declare(strict_types=1);

namespace App\Discord\Plugin\GiveRoles;

use App\Discord\Plugin\AbstractPlugin;
use App\Repository\AssettoCorsaAssociatedNameRepository;
use Discord\Parts\Channel\Message;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Embed\Field;

final class Nick extends AbstractPlugin
{
    private AssettoCorsaAssociatedNameRepository $associatedNameRepository;

    public function __construct(
        AssettoCorsaAssociatedNameRepository $associatedNameRepository
    )
    {
        $this->associatedNameRepository = $associatedNameRepository;
    }

    protected function bind(): void
    {
        $this->discord->on("message", fn(Message $message) => $this->messageHandler($message));
    }

    private function messageHandler(Message $message): void
    {
        if ($this->matcher->isOwnMessage($message) || !$this->matcher->messageHasRequiredContent($message))
            return;

        $memberId = @$this->matcher->getMessageMatches()[1] === null ? $message->author->id : $this->matcher->getMessageMatches()[1];

        $fields = [];
        foreach ($this->associatedNameRepository->findAllByDiscordId($memberId) as $item) {
            $field = new Field($this->discord);
            $field->name = $item->getAssetto();
            $field->value = "<@{$item->getDiscord()}>";
            $fields[] = $field;
        }

        $embed = new Embed($this->discord);
        $embed->color = 3447003;
        $embed->title = "Powiązane nicki w Assetto Corsa";
        $embed->url = "https://www.simss.pl/";
        $embed->fields = $fields;
        $message->channel->sendEmbed($embed);
    }

    public function getMessageEquals(): array
    {
        return [
            "!nick"
        ];
    }

    public function getMessageContainsRegex(): array
    {
        return [
            "/^\!nick ([0-9]+)$/"
        ];
    }
}