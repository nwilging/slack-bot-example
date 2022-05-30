# Laravel Slack Bot

An example project.

---

# Command Handlers
Command handlers can be found in `App\Providers\AppServiceProvider`:
```phpt
<?php

namespace App\Providers;

use App\TestCommandHandler;
use Illuminate\Support\ServiceProvider;
use Nwilging\LaravelSlackBot\Contracts\Services\SlackCommandHandlerFactoryServiceContract;

class AppServiceProvider extends ServiceProvider
{
    protected const SLACK_COMMAND_HANDLERS = [
        'laravel' => TestCommandHandler::class,
    ];

    public function register()
    {
        /** @var SlackCommandHandlerFactoryServiceContract $slackCommandFactory */
        $slackCommandFactory = $this->app->make(SlackCommandHandlerFactoryServiceContract::class);
        foreach (static::SLACK_COMMAND_HANDLERS as $command => $handler) {
            $slackCommandFactory->register($handler, $command);
        }
    }
}
```

# Creating a Command Handler
An example command handler can be found at `App\WeatherCommandHandler`:
```phpt
<?php
declare(strict_types=1);

namespace App;

use Nwilging\LaravelSlackBot\Contracts\SlackCommandHandlerContract;
use Nwilging\LaravelSlackBot\Support\SlackCommandRequest;
use Symfony\Component\HttpFoundation\Response;

class WeatherCommandHandler implements SlackCommandHandlerContract
{
    public function handle(SlackCommandRequest $commandRequest): Response
    {
        // Do something with the command request
        return response(':construction_worker: Got it!');
    }
}
```

# More Information

For more information on this project, check out the blog post [here](https://blog.wilging.org/building-a-slack-bot-with-laravel/).
