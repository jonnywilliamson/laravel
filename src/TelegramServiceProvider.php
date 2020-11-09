<?php

namespace Telegram\Bot\Laravel;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Telegram\Bot\Api;
use Telegram\Bot\Bot;
use Telegram\Bot\BotManager;
use Telegram\Bot\Laravel\Http\Middleware\ValidateWebhook;

/**
 * Class TelegramServiceProvider.
 */
class TelegramServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->registerRoutes();
    }

    /**
     * Register the routes.
     */
    protected function registerRoutes(): void
    {
        Route::group([
            'domain'     => config('telegram.webhook.domain', null),
            'prefix'     => config('telegram.webhook.path'),
            'middleware' => ValidateWebhook::class,
        ], function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/telegram.php');
        });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        $this->configure();
        $this->offerPublishing();
        $this->registerBindings();
        $this->registerCommands();
    }

    /**
     * Setup the configuration.
     */
    protected function configure(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/telegram.php', 'telegram');
    }

    /**
     * Setup the resource publishing groups.
     */
    protected function offerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/telegram.php' => config_path('telegram.php'),
            ], 'telegram-config');
        }
    }

    /**
     * Register bindings in the container.
     */
    protected function registerBindings(): void
    {
        $this->app->bind(
            BotManager::class,
            fn ($app) => (new BotManager(config('telegram')))->setContainer($app)
        );
        $this->app->alias(BotManager::class, 'telegram');

        $this->app->bind(Bot::class, fn ($app) => $app[BotManager::class]->bot());
        $this->app->alias(Bot::class, 'telegram.bot');

        $this->app->bind(Api::class, fn ($app) => $app[Bot::class]->getApi());
        $this->app->alias(Api::class, 'telegram.api');
    }

    /**
     * Register the Artisan commands.
     */
    protected function registerCommands(): void
    {
        $commands = collect(
            [
                Console\Command\CommandListCommand::class,
                Console\Command\CommandMakeCommand::class,
                Console\Command\CommandRegisterCommand::class,
                Console\Webhook\WebhookInfoCommand::class,
                Console\Webhook\WebhookRemoveCommand::class,
                Console\Webhook\WebhookRegisterCommand::class,
            ]
        )
            ->unless($this->app->environment('production'), function (Collection $collection) {
                return $collection->merge([Console\Webhook\WebhookExposeCommand::class]);
            })
            ->toArray();

        if ($this->app->runningInConsole()) {
            $this->commands($commands);
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides(): array
    {
        return [BotManager::class, Bot::class, Api::class, 'telegram', 'telegram.bot', 'telegram.api'];
    }
}
