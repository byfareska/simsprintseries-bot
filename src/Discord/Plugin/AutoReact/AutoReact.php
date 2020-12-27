<?php declare(strict_types=1);

namespace App\Discord\Plugin\AutoReact;

use App\Discord\Plugin\AbstractPlugin;
use App\Entity\AutoReact as AutoReactEntity;
use App\Repository\AutoReactRepository;
use Discord\Parts\Channel\Message;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

final class AutoReact extends AbstractPlugin
{
    /** @var AutoReactEntity[] $autoReactList */
    private array $autoReactList = [];
    private AutoReactRepository $autoReactRepository;
    private EntityManagerInterface $em;

    public function __construct(AutoReactRepository $autoReactRepository, EntityManagerInterface $em)
    {
        $this->autoReactRepository = $autoReactRepository;
        $this->em = $em;
    }

    protected function bind(): void
    {
        $this->discord->on("message", fn(Message $message) => $this->messageHandler($message));
        $this->fetchAutoReactList();
        $this->discord->getLoop()->addPeriodicTimer(60 * 60, fn() => $this->fetchAutoReactList());
    }

    private function messageHandler(Message $message): void
    {
        try {
            if ($this->matcher->isMatch($message))
                $this->addCommand($message);

            foreach ($this->autoReactList as $autoReact) {
                if (str_contains(strtolower($message->content), $autoReact->getMessage()))
                    $message->react($autoReact->getReact());
            }

        } catch (Exception $e) {
            echo "{$e->getMessage()}\n";
        }
    }

    public function getMessageEquals(): array
    {
        return [
            "!autoreact"
        ];
    }

    public function getMessageContainsRegex(): array
    {
        return [
            "/^\!autoreact \"(.+)\" \"(.+)\"$/"
        ];
    }

    private function fetchAutoReactList(): void
    {
        $this->autoReactList = $this->autoReactRepository->findAll();
    }

    private function addCommand(Message $message): void
    {
        if (@$this->matcher->getMessageMatches()[2] === null) {
            $message->reply("Błędna składnia.");
            return;
        }

        $ar = new AutoReactEntity();
        $ar->setMessage(strtolower($this->matcher->getMessageMatches()[1]));
        $ar->setReact(str_replace([">", "<"], ["",""], trim($this->matcher->getMessageMatches()[2])));
        $this->em->persist($ar);
        $this->em->flush();
        $this->fetchAutoReactList();
        $message->react("✅");
    }
}