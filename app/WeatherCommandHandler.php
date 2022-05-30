<?php
declare(strict_types=1);

namespace App;

use App\Jobs\WeatherQueryJob;
use Illuminate\Contracts\Bus\Dispatcher;
use Nwilging\LaravelSlackBot\Contracts\SlackCommandHandlerContract;
use Nwilging\LaravelSlackBot\Support\SlackCommandRequest;
use Symfony\Component\HttpFoundation\Response;

class WeatherCommandHandler implements SlackCommandHandlerContract
{
    protected Dispatcher $dispatcher;

    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function handle(SlackCommandRequest $commandRequest): Response
    {
        $locationQuery = (!empty($commandRequest->commandArgs)) ? $commandRequest->commandArgs[0] : null;
        if (!$locationQuery) {
            return response(':warning: Oops! A location query is required. (e.g. `/weather 90210`)');
        }

        $this->dispatcher->dispatch(new WeatherQueryJob($locationQuery, $commandRequest));
        return response(':construction_worker: Fetching...');
    }
}
