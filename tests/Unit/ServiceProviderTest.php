<?php

use Telegram\Bot\Api;
use Telegram\Bot\Bot;
use Telegram\Bot\BotManager;
use Telegram\Bot\Laravel\Facades\Telegram;

test('it binds bot manager to the container', function () {
    expect($this->app->make(BotManager::class))->toBeInstanceOf(BotManager::class);
    expect($this->app->make('telegram'))->toBeInstanceOf(BotManager::class);
});

test('it binds default bot to the container', function () {
    expect($this->app->make(Bot::class))->toBeInstanceOf(Bot::class);
    expect($this->app->make('telegram.bot'))->toBeInstanceOf(Bot::class);
});

test('it binds api to the container', function () {
    expect($this->app->make(Api::class))->toBeInstanceOf(Api::class);
    expect($this->app->make('telegram.api'))->toBeInstanceOf(Api::class);
});

test('the telegram facade resolves to bot manager', function () {
    expect(Telegram::getFacadeRoot())->toBeInstanceOf(BotManager::class);
});
