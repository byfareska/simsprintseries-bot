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

    public function __construct(string $name = null, Discord $discord, iterable $plugins = [], PermissionsChecker $permissionsChecker)
    {
        $this->plugins = $plugins;
        $this->discord = $discord;
        $this->permissionsChecker = $permissionsChecker;
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;

        $this->discord->on('ready', function () use ($output) {
            echo "Bot is ready! Logged in as {$this->discord->user->username}!", PHP_EOL;
        });

        $this->initPlugins();
        $this->discord->run();
        return self::SUCCESS;
    }

    protected function initPlugins(): void
    {
        foreach ($this->plugins as $plugin) {
            try {
                $plugin->init($this->discord, $this->input, $this->output, $this->permissionsChecker);
            } catch (Throwable $e) {
                echo "{$e->getMessage()}\n";
            }
        }
    }
}