<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Nwilging\LaravelSlackBot\Contracts\Notifications\SlackApiNotificationContract;
use Nwilging\LaravelSlackBot\Contracts\Support\LayoutBuilder\BuilderContract;
use Nwilging\LaravelSlackBot\Support\LayoutBlocks\Blocks\SectionBlock;
use Nwilging\LaravelSlackBot\Support\LayoutBlocks\Elements\ButtonElement;
use Nwilging\LaravelSlackBot\Support\LayoutBuilder\Builder;
use Nwilging\LaravelSlackBot\Support\SlackOptionsBuilder;

class TestNotification extends Notification implements SlackApiNotificationContract
{
    use Queueable;

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['slackBot'];
    }

    public function toSlackArray($notifiable): array
    {
        $options = new SlackOptionsBuilder();
        $options->username('My Bot')
            ->iconEmoji(':smile:');

        $layoutBuilder = new Builder();

        $sectionBlock = new SectionBlock();
        $sectionBlock->withFields([
            $layoutBuilder->withPlainText('Some text in a section'),
        ]);

        $accessoryButton = new ButtonElement($layoutBuilder->withPlainText('A Button'), 'actionId');
        $accessoryButton->primary();

        $sectionBlock->withAccessory($accessoryButton);

        $layoutBuilder
            ->header('Test Message')
            ->divider()
            ->addBlock($sectionBlock);

        return [
            'contentType' => 'blocks',
            'blocks' => $layoutBuilder->getBlocks(),
            'channelId' => 'relocity-test-2',
            'options' => $options->toArray(),
        ];
    }
}
