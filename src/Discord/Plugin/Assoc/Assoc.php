<?php declare(strict_types=1);

namespace App\Discord\Plugin\Assoc;

use App\Discord\Plugin\AbstractPlugin;
use App\Discord\Utility\Plugin\PermissionsChecker;
use App\Entity\AssettoCorsaAssociatedName;
use App\Repository\AssettoCorsaAssociatedNameRepository;
use Discord\Parts\Channel\Message;
use Doctrine\ORM\EntityManagerInterface;

final class Assoc extends AbstractPlugin
{
    private AssettoCorsaAssociatedNameRepository $associatedNameRepository;
    private EntityManagerInterface $em;

    public function __construct(AssettoCorsaAssociatedNameRepository $associatedNameRepository, EntityManagerInterface $em)
    {
        $this->em = $em;
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

        $this->permissions->executeIfAdmin(
            $message->author,
            fn() => $this->messageHandlerAfterPermissionCheck($message),
            fn() => $message->reply("nie masz uprawnień by korzystać z tej komendy.")
        );
    }

    private function messageHandlerAfterPermissionCheck(Message $message): void
    {
        if (!isset($this->matcher->getMessageMatches()[2])) {
            $message->reply("błędna składnia. Użycie komendy\n```!assoc add discordid nick (np. !assoc add 634699926978166796 eska)\n!assoc rm nick np. (!assoc rm eska)```");
            return;
        }

        switch ($this->matcher->getMessageMatches()[1]) {
            case "add":
                $this->addAction($message);
                return;
            case "rm":
                $this->rmAction($message);
                return;
            default:
                $message->reply("Nieznana akcja \"{$this->matcher->getMessageMatches()[1]}\".");
        }
    }

    public function getMessageContainsRegex(): array
    {
        return [
            "/^\!assoc ([a-z]+) ([0-9]+) (.+)$/",
            "/^\!assoc ([a-z]+) (.+)$/"
        ];
    }

    public function getMessageEquals(): array
    {
        return [
            "!assoc"
        ];
    }

    private function addAction(Message $message): void
    {
        $entry = new AssettoCorsaAssociatedName();
        $entry->setAssetto($this->matcher->getMessageMatches()[3]);
        $entry->setDiscord($this->matcher->getMessageMatches()[2]);
        $this->em->persist($entry);
        $this->em->flush();
        $message->react("✅");
    }

    private function rmAction(Message $message): void
    {
        $entry = $this->associatedNameRepository->findOneByAssettoCorsaName($this->matcher->getMessageMatches()[2]);
        if ($entry === null) {
            $message->reply("nie ma żadnego wpisu o nicku \"{$this->matcher->getMessageMatches()[2]}\".");
            return;
        }

        $message->reply("Usuwanie wpisu {$entry->getAssetto()}, {$entry->getDiscord()} #{$entry->getId()}.");
        $this->em->remove($entry);
        $this->em->flush();
    }
}