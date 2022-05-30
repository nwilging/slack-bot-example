<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Nwilging\LaravelSlackBot\Contracts\Services\SlackCommandHandlerServiceContract;
use Symfony\Component\HttpFoundation\Response;

class SlackCommandController extends Controller
{
    protected SlackCommandHandlerServiceContract $slackCommandHandler;

    public function __construct(SlackCommandHandlerServiceContract $slackCommandHandler)
    {
        $this->slackCommandHandler = $slackCommandHandler;
    }

    public function __invoke(Request $request): Response
    {
        return $this->slackCommandHandler->handle($request);
    }
}
