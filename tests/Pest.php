<?php

use Orchestra\Testbench\TestCase;
use Telegram\Bot\Api;
use Telegram\Bot\Bot;
use Telegram\Bot\BotManager;
use Telegram\Bot\Laravel\TelegramServiceProvider;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The tests in this file belong to the Testbench test case.
|
*/

uses(TestCase::class)
    ->beforeEach(function () {
        // Automatically load the package service provider for all tests in 'Unit' and 'Feature'
        $this->app->register(TelegramServiceProvider::class);
        $this->app->alias(BotManager::class, 'telegram');
        $this->app->alias(Bot::class, 'telegram.bot');
        $this->app->alias(Api::class, 'telegram.api');
    })
    ->in('Unit', 'Feature'); // Apply to tests in 'Unit' and 'Feature' directories
