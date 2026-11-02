<?php

namespace NotificationChannels\SmsRu;

use Illuminate\Events\Dispatcher;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Notifications\Notification;
use NotificationChannels\SmsRu\Exceptions\CouldNotSendNotification;

class SmsRuChannel
{
    /**
     * @var SmsRuApi
     */
    protected $smsApi;

    /**
     * @var Dispatcher
     */
    protected $events;

    /**
     * SmsRuChannel constructor.
     * @param SmsRuApi $smsApi
     * @param Dispatcher $events
     */
    public function __construct(SmsRuApi $smsApi, Dispatcher $events)
    {
        $this->smsApi = $smsApi;
        $this->events = $events;
    }

    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  Notification&SmsRuNotification  $notification
     *
     * @throws CouldNotSendNotification
     *
     * @return array|null
     */
    public function send($notifiable, Notification $notification)
    {
        try {
            $recipient = $this->getRecipient($notifiable);
            $message = $notification->toSmsru($notifiable);

            if (is_string($message)) {
                $message = new SmsRuMessage($message);
            }
            if (!$message instanceof SmsRuMessage) {
                throw CouldNotSendNotification::invalidMessageObject($message);
            }

            return $this->smsApi->sendMessage($recipient, $message);
        } catch (\Exception $exception) {
            $event = new NotificationFailed($notifiable, $notification, 'smsru', ['message' => $exception->getMessage(), 'exception' => $exception]);

            $this->events->dispatch($event);
        }
    }

    protected function getRecipient($notifiable)
    {
        if ($notifiable->routeNotificationFor('smsru')) {
            return $notifiable->routeNotificationFor('smsru');
        }

        if (isset($notifiable->phone)) {
            return $notifiable->phone;
        }

        throw CouldNotSendNotification::invalidReceiver();
    }
}
