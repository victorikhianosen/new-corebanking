<?php

namespace App\Services\Message;

use App\Mail\CoreBankMail;
use App\Mail\SystemNotificationMail;
use App\Models\Message;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class MessageService
{
    public function send(
        Model $actor,
        string $channel,
        string $type,
        string $recipient,
        string $body,
        ?string $subject = null,
        array $payload = []
    ): Message {
        $message = Message::create([
            'actor_id'   => $actor->id,
            'actor_type' => get_class($actor),
            'channel'    => $channel,
            'type'       => $type,
            'recipient'  => $recipient,
            'subject'    => $subject,
            'body'       => $body,
            'payload'    => $payload,
            'status'     => 'pending',
        ]);

        try {
            match ($channel) {
                'email'  => $this->sendEmail($message),
                'sms'    => $this->sendSms($message),
                'push'   => $this->sendPush($message),
                'in_app' => $this->sendInApp($message),
                default  => $this->sendEmail($message),
            };

            $message->update([
                'status'  => 'sent',
                'sent_at' => now(),
            ]);
        } catch (Throwable $e) {
            $message->update([
                'status'         => 'failed',
                'failure_reason' => $e->getMessage(),
            ]);

            Log::error('Message send failed', [
                'message_id' => $message->id,
                'channel'    => $channel,
                'error'      => $e->getMessage(),
            ]);
        }

        return $message->fresh();
    }


    protected function sendEmail(Message $message): void
{
    Mail::to($message->recipient)->send(new CoreBankMail(
        subjectLine: $message->subject ?? 'Notification',
        content: $message->body,
        files: $message->payload['files'] ?? [],
    ));
}


    protected function sendSms(Message $message): void
    {
    }

    protected function sendPush(Message $message): void
    {
    }

    protected function sendInApp(Message $message): void
    {
    }
}