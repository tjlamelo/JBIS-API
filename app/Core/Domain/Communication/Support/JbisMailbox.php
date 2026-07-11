<?php

declare(strict_types=1);

namespace App\Core\Domain\Communication\Support;

use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Envelope;
use InvalidArgumentException;

final class JbisMailbox
{
    public static function domain(): string
    {
        return (string) config('mailboxes.domain', 'jbis.cm');
    }

    public static function address(string $key): string
    {
        $entry = config("mailboxes.addresses.{$key}");

        if (! is_array($entry) || empty($entry['address'])) {
            throw new InvalidArgumentException("Adresse mail inconnue : {$key}");
        }

        return (string) $entry['address'];
    }

    public static function name(string $key): string
    {
        $entry = config("mailboxes.addresses.{$key}");

        return is_array($entry) ? (string) ($entry['name'] ?? 'JBIS') : 'JBIS';
    }

    public static function from(string $key): Address
    {
        return new Address(self::address($key), self::name($key));
    }

    public static function routeAddress(string $purpose): string
    {
        $mailboxKey = config("mailboxes.routing.{$purpose}");

        return self::address(is_string($mailboxKey) && $mailboxKey !== '' ? $mailboxKey : 'contact');
    }

    public static function transactionalEnvelope(string $subject, ?string $replyToKey = null): Envelope
    {
        $fromKey = (string) config('mailboxes.routing.transactional', 'noreply');
        $replyKey = $replyToKey ?? (string) config('mailboxes.routing.reply_to_default', 'contact');

        return new Envelope(
            from: self::from($fromKey),
            replyTo: [self::from($replyKey)],
            subject: $subject,
        );
    }

    /**
     * @return list<array{key: string, address: string, name: string}>
     */
    public static function publicAddresses(): array
    {
        $addresses = config('mailboxes.addresses', []);
        $out = [];

        foreach ($addresses as $key => $entry) {
            if (! is_array($entry) || empty($entry['address'])) {
                continue;
            }
            if (($entry['public'] ?? false) !== true) {
                continue;
            }
            $out[] = [
                'key' => (string) $key,
                'address' => (string) $entry['address'],
                'name' => (string) ($entry['name'] ?? 'JBIS'),
            ];
        }

        return $out;
    }
}
