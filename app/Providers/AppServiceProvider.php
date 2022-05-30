<?php

namespace App\Providers;

use App\Jobs\WeatherQueryJob;
use App\WeatherCommandHandler;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Illuminate\Support\ServiceProvider;
use Nwilging\LaravelSlackBot\Contracts\Services\SlackCommandHandlerFactoryServiceContract;
use Nwilging\LaravelSlackBot\Contracts\SlackApiServiceContract;

class AppServiceProvider extends ServiceProvider
{
    protected const SLACK_COMMAND_HANDLERS = [
        'weather' => WeatherCommandHandler::class,
    ];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(ClientInterface::class, Client::class);

        $this->app->bindMethod(sprintf('%s@handle', WeatherQueryJob::class), function (WeatherQueryJob $job): void {
            $job->handle(
                $this->app->make(ClientInterface::class),
                $this->app->make(SlackApiServiceContract::class),
                env('WEATHER_STACK_API_KEY')
            );
        });

        /** @var SlackCommandHandlerFactoryServiceContract $slackCommandFactory */
        $slackCommandFactory = $this->app->make(SlackCommandHandlerFactoryServiceContract::class);
        foreach (static::SLACK_COMMAND_HANDLERS as $command => $handler) {
            $slackCommandFactory->register($handler, $command);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
