<?php

namespace NotificationChannels\SmsRu;

/**
 * Contract for notifications that can be sent through the SMS.ru channel.
 */
interface SmsRuNotification
{
    /**
     * Build the SMS.ru message for the given notifiable.
     *
     * @param mixed $notifiable
     * @return SmsRuMessage|string
     */
    public function toSmsru($notifiable);
}
