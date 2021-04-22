<?php declare(strict_types=1);

namespace App\Command;

use App\Discord\Plugin\PluginInterface;
use App\Discord\Utility\Plugin\PermissionsChecker;
use Discord\Discord;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

final class AppCommand extends Command
{
    /**
     * @var PluginInterface[]
     */
    private iterable $plugins;
    protected static $defaultName = "app";
    private Discord $discord;
    private ?InputInterface $input = null;
    private ?OutputInterface $output = null;
    private PermissionsChecker $permissionsChecker;

    public function __construct(
        string $name = null,
        iterable $plugins = [],
        PermissionsChecker $permissionsChecker,
        Discord $discord
    )
    {
        $this->discord = $discord;
        $this->plugins = $plugins;
        $this->permissionsChecker = $permissionsChecker;
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->input = $input;
            $this->output = $output;

            $this->discord->on('ready', function () use ($output) {
                $this->output->writeln("Bot is ready! Logged in as {$this->discord->user->username}!");
                $this->initPlugins();
            });

            $this->discord->run();
        } catch (Throwable $e) {
            dump($e);
        }
        return self::SUCCESS;
    }

    protected function initPlugins(): void
    {
        foreach ($this->plugins as $plugin) {
            $plugin->init($this->discord, $this->input, $this->output, $this->permissionsChecker);
            $this->output->writeln("Loaded plugin " . get_class($plugin));
        }
    }
}