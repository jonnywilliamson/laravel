<?php

namespace Telegram\Bot\Laravel\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Symfony\Component\Console\Input\InputArgument;
use Telegram\Bot\Bot;
use Telegram\Bot\BotManager;
use Telegram\Bot\Exceptions\TelegramSDKException;

class ConsoleBaseCommand extends Command
{
    protected BotManager $manager;

    public function __construct(BotManager $manager)
    {
        parent::__construct();

        $this->manager = $manager;
    }

    /**
     * @throws TelegramSDKException
     * @throws BindingResolutionException
     */
    public function bot(): Bot
    {
        return $this->manager->bot($this->argument('bot'));
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments(): array
    {
        return [
            ['bot', InputArgument::OPTIONAL, 'The bot name defined in config'],
        ];
    }
}
