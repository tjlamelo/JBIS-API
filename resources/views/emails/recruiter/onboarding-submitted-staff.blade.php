<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nouvelle demande recruteur — {{ $brandName }}</title>
    <link rel="icon" href="{{ $logoUrl }}">
</head>
<body style="margin:0; padding:0; font-family:Arial, sans-serif; background:#f4f6f8;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f4f6f8; padding:24px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="680" cellspacing="0" cellpadding="0" border="0" style="width:100%; max-width:680px; background:#ffffff; border-radius:10px; overflow:hidden;">
                @include('emails.partials.brand-header', ['headerSubtitle' => 'Nouvelle demande recruteur'])
                <tr>
                    <td style="padding:28px 24px; color:#111827; font-size:15px; line-height:1.6;">
                        <p style="margin:0 0 12px;">Une nouvelle demande d'accès au portail recruteur a été soumise.</p>
                        <ul style="margin:0 0 20px; padding-left:18px;">
                            <li><strong>Entreprise :</strong> {{ $application->company_name }}</li>
                            <li><strong>Contact :</strong> {{ $application->contact_name }} ({{ $application->contact_email }})</li>
                            <li><strong>Téléphone :</strong> {{ $application->contact_phone ?? '—' }}</li>
                            <li><strong>Statut :</strong> {{ $application->status?->value ?? $application->status }}</li>
                        </ul>
                        <p style="margin:0;">
                            <a href="{{ $reviewUrl }}" style="display:inline-block; background:#0f172a; color:#ffffff; text-decoration:none; padding:10px 16px; border-radius:6px; font-weight:600;">
                                Analyser la demande
                            </a>
                        </p>
                    </td>
                </tr>
                @include('emails.partials.brand-footer')
            </table>
        </td>
    </tr>
</table>
</body>
</html>
