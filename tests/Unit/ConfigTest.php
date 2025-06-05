<?php

use Telegram\Bot\Commands\HelpCommand;
use Telegram\Bot\Laravel\Http\Controllers\WebhookController;
use Telegram\Bot\Laravel\TelegramServiceProvider;

// This will apply to all tests in this file
beforeEach(function () {
    $this->app->register(TelegramServiceProvider::class);
});


test('it loads the default bot configuration', function () {
    $config = $this->app['config']->get('telegram.bots.default');
    expect($config)->toBeArray();
    expect($config)->toHaveKey('token');
    expect($config['token'])->toBe('YOUR-BOT-TOKEN');
});

test('it loads the second bot configuration', function () {
    $config = $this->app['config']->get('telegram.bots.second');
    expect($config)->toBeArray();
    expect($config)->toHaveKey('token');
    expect($config['token'])->toBe('123456:abc');
});

test('it loads the webhook configuration', function () {
    $config = $this->app['config']->get('telegram.webhook');
    expect($config)->toBeArray();
    expect($config)->toHaveKey('path');
    expect($config['path'])->toBe('telegram');
    expect($config['controller'])->toBe(WebhookController::class);
});

test('it loads the global commands', function () {
    $commands = $this->app['config']->get('telegram.commands');
    expect($commands)->toBeArray();
    expect($commands)->toHaveKey('help');
    expect($commands['help'])->toBe(HelpCommand::class);
});