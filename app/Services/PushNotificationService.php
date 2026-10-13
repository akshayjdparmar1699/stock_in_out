<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Support\Collection;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

/**
 * Sends a real OS/browser-level push notification to every device a user
 * has subscribed from. Does nothing if VAPID keys aren't configured yet
 * (fresh install, or a dev environment that hasn't set them up) rather
 * than throwing — push is an enhancement, never something a save should
 * fail over.
 */
class PushNotificationService
{
    public static function sendToUser(User $user, string $title, string $body, ?string $url = null): void
    {
        if (! config('services.webpush.public_key') || ! config('services.webpush.private_key')) {
            return;
        }

        $subscriptions = PushSubscription::where('user_id', $user->id)->get();

        if ($subscriptions->isEmpty()) {
            return;
        }

        self::send($subscriptions, $title, $body, $url);
    }

    /**
     * @param  Collection<int, User>  $users
     */
    public static function sendToUsers(Collection $users, string $title, string $body, ?string $url = null): void
    {
        foreach ($users as $user) {
            self::sendToUser($user, $title, $body, $url);
        }
    }

    private static function send(Collection $subscriptions, string $title, string $body, ?string $url): void
    {
        $webPush = new WebPush([
            'VAPID' => [
                'subject' => config('services.webpush.subject'),
                'publicKey' => config('services.webpush.public_key'),
                'privateKey' => config('services.webpush.private_key'),
            ],
        ]);

        $payload = json_encode(['title' => $title, 'body' => $body, 'url' => $url]);

        foreach ($subscriptions as $subscription) {
            $webPush->queueNotification(
                Subscription::create(array_filter([
                    'endpoint' => $subscription->endpoint,
                    'publicKey' => $subscription->public_key,
                    'authToken' => $subscription->auth_token,
                    'contentEncoding' => $subscription->content_encoding,
                ])),
                $payload
            );
        }

        foreach ($webPush->flush() as $report) {
            if (! $report->isSuccess() && $report->isSubscriptionExpired()) {
                PushSubscription::where('endpoint', (string) $report->getRequest()->getUri())->delete();
            }
        }
    }
}
