<?php declare(strict_types=1);

namespace App\Discord\Plugin\AutoResponder;

use App\Discord\Plugin\AbstractPlugin;
use App\Entity\AutoResponder as AutoResponderEntity;
use App\Repository\AutoResponderRepository;
use Discord\Parts\Channel\Message;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

final class AutoResponder extends AbstractPlugin
{
    /** @var AutoResponderEntity[] $autoResponderList */
    private array $autoResponderList = [];
    private AutoResponderRepository $autoResponderRepository;
    private EntityManagerInterface $em;

    public function __construct(AutoResponderRepository $autoResponderRepository, EntityManagerInterface $em)
    {
        $this->autoResponderRepository = $autoResponderRepository;
        $this->em = $em;
    }

    protected function bind(): void
    {
        $this->discord->on("message", fn(Message $message) => $this->messageHandler($message));
        $this->discord->getLoop()->addPeriodicTimer(60 * 60, fn() => $this->fetchAutoResponderList());
    }

    private function messageHandler(Message $message): void
    {
        try {
            if ($this->matcher->isOwnMessage($message))
                return;

            if ($this->matcher->isMatch($message))
                $this->addCommand($message);

            foreach ($this->autoResponderList as $autoResponder) {
                if (strtolower(trim($message->content)) === $autoResponder->getMessage())
                    $message->reply($autoResponder->getResponse());
            }

        } catch (Exception $e) {
            echo "{$e->getMessage()}\n";
        }
    }

    public function getMessageEquals(): array
    {
        return [
            "!autoresponder"
        ];
    }

    public function getMessageContainsRegex(): array
    {
        return [
            "/^\!autoresponder \"(.+)\" \"(.+)\"$/"
        ];
    }

    private function fetchAutoResponderList(): void
    {
        $this->autoResponderList = $this->autoResponderRepository->findAll();
    }

    private function addCommand(Message $message): void
    {
        if (@$this->matcher->getMessageMatches()[2] === null) {
            $message->reply("Błędna składnia.");
            return;
        }

        $ar = new AutoResponderEntity();
        $ar->setMessage(strtolower($this->matcher->getMessageMatches()[1]));
        $ar->setResponse($this->matcher->getMessageMatches()[2]);
        $this->em->persist($ar);
        $this->em->flush();
        $this->fetchAutoResponderList();
        $message->react("✅");
    }
}