<?php
declare(strict_types=1);

namespace App\Jobs;

use GuzzleHttp\ClientInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Nwilging\LaravelSlackBot\Contracts\SlackApiServiceContract;
use Nwilging\LaravelSlackBot\Support\LayoutBlocks\Blocks\SectionBlock;
use Nwilging\LaravelSlackBot\Support\LayoutBlocks\Elements\ImageElement;
use Nwilging\LaravelSlackBot\Support\LayoutBuilder\Builder;
use Nwilging\LaravelSlackBot\Support\SlackCommandRequest;

class WeatherQueryJob implements ShouldQueue
{
    use SerializesModels;

    protected string $query;

    protected SlackCommandRequest $request;

    public function __construct(string $query, SlackCommandRequest $request)
    {
        $this->query = $query;
        $this->request = $request;
    }

    public function handle(ClientInterface $httpClient, SlackApiServiceContract $slackApiService, string $weatherStackApiKey): void
    {
        $weatherStackUrl = sprintf(
            'http://api.weatherstack.com/current?access_key=%s&query=%s',
            $weatherStackApiKey,
            $this->query
        );

        $result = $httpClient->request('GET', $weatherStackUrl);
        $data = json_decode($result->getBody()->getContents());

        $layoutBuilder = new Builder();
        $layoutBuilder->header(sprintf('%s Current weather for %s, %s', $this->getWeatherIcon($data), $data->location->name, $data->location->region));
        $layoutBuilder->divider();

        $weatherDescription = "*" . $data->current->observation_time . "*\r\n"
            . "*Temperature:* " . $this->cToF((float) $data->current->temperature)
            . "°F (feels like " . $this->cToF((float) $data->current->feelslike) . "°F)\r\n"
            . "*Wind Speed:* " . $this->kmhToMph((float) $data->current->wind_speed) . " MPH\r\n"
            . "*Humidity:* " . $data->current->humidity . "%";

        $section1 = new SectionBlock();
        $section1->withText($layoutBuilder->withMarkdownText($weatherDescription));
        if (!empty($data->current->weather_icons)) {
            $image = new ImageElement($data->current->weather_icons[0], 'Weather Icon');
            $section1->withAccessory($image);
        }

        $layoutBuilder->addBlock($section1);
        $slackApiService->sendBlocksMessage($this->request->channelId, $layoutBuilder->getBlocks());
    }

    protected function getWeatherIcon(\stdClass $data): string
    {
        $descriptions = $data->current->weather_descriptions;
        if (empty($descriptions)) {
            return '';
        }

        foreach ($descriptions as $description) {
            switch (true) {
                case stripos($description, 'partly cloudy') !== false:
                    return ':sun_small_cloud:';
                case stripos($description, 'cloudy') !== false:
                    return ':cloud:';
                case stripos($description, 'partly sunny') !== false:
                    return ':partly_sunny:';
                case stripos($description, 'sunny') !== false:
                    return ':sunny:';
            }
        }

        return '';
    }

    protected function cToF(float $c): float
    {
        return (float) (($c * (9/5)) + 32);
    }

    protected function kmhToMph(float $kmh): float
    {
        return round((float) ($kmh / 1.609), 2);
    }
}
