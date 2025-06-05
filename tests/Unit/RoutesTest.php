<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Telegram\Bot\Bot;
use Telegram\Bot\BotManager;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Laravel\Http\Controllers\WebhookController;
use Telegram\Bot\Laravel\Http\Middleware\ValidateWebhook;
use Telegram\Bot\Objects\ResponseObject;

// Test that the routes are registered and middleware is attached
it('registers telegram webhook route with middleware', function () {
    $routes = collect(Route::getRoutes()->get() ?: []);
    $webhookRoute = $routes->firstWhere('uri', 'telegram/{bot}/webhook');

    expect($webhookRoute)->not->toBeNull()
        ->and($webhookRoute->methods())->toContain('POST')
        ->and(collect($webhookRoute->middleware())->contains(ValidateWebhook::class))->toBeTrue();
});

// Test webhook controller handles a request
it('webhook controller listen handles update', function () {
    $botName = 'default';
    $manager = $this->app->make(BotManager::class);
    $controller = new WebhookController();

    // Simulate lifecycle termination callback invocation
    $invoked = false;
    App::terminating(function () use (&$invoked, $manager, $botName) {
        $invoked = true;
        $bot = $manager->bot($botName);
        expect($bot)->toBeInstanceOf(Bot::class);
    });

    $response = $controller->__invoke($manager, $botName);

    expect($response->getStatusCode())->toBe(204); // no content
    expect($invoked)->toBeTrue();
})
    ->todo('To work on');

// Console Command: telegram:webhook:setup - success scenario
it('executes telegram:webhook:setup command successfully', function () {
    $this->artisan('telegram:webhook:setup')->assertExitCode(0)->expectsOutputToContain('Setting webhook for');
})
    ->todo('Making an actual outbound request. Needs to be mocked');

// Console Command: telegram:webhook:remove - confirmation rejected scenario
it('does not remove webhook if confirmation rejected', function () {
    $command = $this->artisan('telegram:webhook:remove');
    $command->expectsQuestion('Are you sure you want to remove the webhook for [default] bot?', false);
    $command->assertExitCode(0);
});

// Console Command: telegram:command:register - success scenario
it('executes telegram:command:register command successfully', function () {
    $this->artisan('telegram:command:register')->assertExitCode(0)->expectsOutputToContain('Commands Registered Successfully!');
})
    ->todo('Making an actual outbound request. Needs to be mocked');

// Console Command: telegram:command:list - lists commands
it('executes telegram:command:list command successfully and lists commands', function () {
    $this->artisan('telegram:command:list')->assertExitCode(0);
})
    ->todo('needs to add expect table');

// Facade Telegram fake test
it('telegram facade fake returns predefined response', function () {
    $tgFake = Telegram::fake([
        new ResponseObject(['id' => 1, 'first_name' => 'Test', 'username' => 'testbot']),
    ]);

    $response = Telegram::sendMessage([
        'chat_id' => 123456789,
        'text'    => 'Hello Pest Test',
    ]);

    expect($response->id)->toBe(1);
    expect($response->first_name)->toBe('Test');
    $tgFake->assertSent('sendMessage');
});

// Middleware test for ValidateWebhook
it('validate webhook middleware rejects invalid secret token', function () {
    // Create a test route that uses the middleware
    Route::post('/test-webhook/{bot}', fn () => response('OK'))->middleware(ValidateWebhook::class);

    // Make a request with invalid secret token
    $response = $this->postJson('/test-webhook/default', [], [
        'X-Telegram-Bot-Api-Secret-Token' => 'invalid-token',
    ]);

    // Should get 403 forbidden
    $response->assertStatus(403);
});

it('validate webhook middleware allows valid secret token', function () {
    Route::post('/test-webhook/{bot}', fn () => response('OK'))->middleware(ValidateWebhook::class);

    $fullBotToken = '123456:secretpart';
    config(['telegram.bots.default.token' => $fullBotToken]);

    $response = $this->postJson('/test-webhook/default', [], [
        'X-Telegram-Bot-Api-Secret-Token' => Str::after($fullBotToken, ':'),
    ]);

    $response->assertStatus(200);
    $response->assertSeeText('OK');
});