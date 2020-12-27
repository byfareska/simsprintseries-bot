<?php

namespace App\Discord\Plugin;

use App\Discord\Utility\Plugin\PermissionsChecker;
use Discord\Discord;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface PluginInterface
{
    public function init(Discord $discord, InputInterface $input, OutputInterface $output, PermissionsChecker $permissionsChecker);
}