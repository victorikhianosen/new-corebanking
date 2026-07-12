<?php

namespace App\Services\Message;

use App\Mail\CoreBankMail;
use App\Models\Message;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class MessageService
{
    public function sendEmail(
        Model $actor,
        string $type,
        string $recipient,
        string $body,
        ?string $subject = null,
        array $payload = []
    ): Message {
        $message = Message::create([
            'actor_id'   => $actor->id,
            'actor_type' => get_class($actor),
            'channel'    => 'email',
            'type'       => $type,
            'recipient'  => $recipient,
            'subject'    => $subject,
            'body'       => $body,
            'payload'    => $payload,
            'status'     => 'pending',
        ]);

        try {
            Mail::to($message->recipient)->send(new CoreBankMail(
                subjectLine: $message->subject ?? 'Notification',
                content: $message->body,
                files: $message->payload['files'] ?? [],
            ));

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
                'channel'    => 'email',
                'error'      => $e->getMessage(),
            ]);
        }

        return $message->fresh();
    }
}