<?php declare(strict_types=1);

namespace App\Discord\Utility\Plugin;

use App\Helper\TextHelper;
use Discord\Discord;
use Discord\Parts\Channel\Message;

final class MessageMatcher
{
    private Discord $discord;
    private array $messageMatches = [];
    private array $messageEquals = [];
    private array $messageContains = [];
    private array $messageContainsRegex = [];
    private array $pushToHelp = [];

    public function __construct(Discord $discord)
    {
        $this->discord = $discord;
    }

    public function isMatch(Message $message, bool $ignoreOwnMessages = true): bool
    {
        if ($ignoreOwnMessages && $this->isOwnMessage($message))
            return false;

        return $this->messageHasRequiredContent($message);
    }

    public function getMessageMatches(): array
    {
        return $this->messageMatches;
    }

    public function isOwnMessage(Message $message): bool
    {
        return $message->author->id === $this->discord->user->id;
    }

    public function messageHasRequiredContent(Message $message): bool
    {
        $contains = function (string $content): bool {
            foreach ($this->getMessageContains() as $searchFor) {
                if (str_contains($content, $searchFor))
                    return true;
            }

            return false;
        };

        $containsRegex = function (string $content): bool {
            foreach ($this->getMessageContainsRegex() as $searchFor) {
                if (preg_match($searchFor, $content, $this->messageMatches) > 0)
                    return true;
            }

            return false;
        };

        $normalizedContent = TextHelper::normalize($message->content);

        if (in_array($normalizedContent, $this->getMessageEquals()) || $contains($normalizedContent) || $containsRegex($message->content))
            return true;

        return false;
    }

    public function getMessageContains(): array
    {
        return $this->messageContains;
    }

    public function getMessageContainsRegex(): array
    {
        return $this->messageContainsRegex;
    }

    public function getMessageEquals(): array
    {
        return $this->messageEquals;
    }

    public function getPushToHelp(): array
    {
        return $this->pushToHelp;
    }

    public function setMessageMatches(array $messageMatches): MessageMatcher
    {
        $this->messageMatches = $messageMatches;
        return $this;
    }

    public function setMessageEquals(array $messageEquals): MessageMatcher
    {
        $this->messageEquals = $messageEquals;
        return $this;
    }

    public function setMessageContainsRegex(array $messageContainsRegex): MessageMatcher
    {
        $this->messageContainsRegex = $messageContainsRegex;
        return $this;
    }

    public function setMessageContains(array $messageContains): MessageMatcher
    {
        $this->messageContains = $messageContains;
        return $this;
    }

    public function setPushToHelp(array $pushToHelp): MessageMatcher
    {
        $this->pushToHelp = $pushToHelp;
        return $this;
    }
}