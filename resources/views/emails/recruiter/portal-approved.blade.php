<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Portail recruteur JBIS</title>
    <link rel="icon" href="{{ $logoUrl }}">
</head>
<body style="margin:0; padding:0; font-family:Arial, sans-serif; background:#f4f6f8;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f4f6f8; padding:24px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="680" cellspacing="0" cellpadding="0" border="0" style="width:100%; max-width:680px; background:#ffffff; border-radius:10px; overflow:hidden;">
                <tr>
                    <td style="background:#0f172a; color:#ffffff; padding:18px 24px; text-align:center;">
                        <img src="{{ $logoUrl }}" alt="Logo JBIS" style="display:block; margin:0 auto 10px; width:64px; height:64px; object-fit:contain;">
                        <h1 style="margin:0; font-size:20px; font-weight:700;">Demande approuvée</h1>
                    </td>
                </tr>
                <tr>
                    <td style="padding:28px 24px; color:#111827; font-size:15px; line-height:1.6;">
                        <p style="margin:0 0 12px;">Bonjour {{ $user->name }},</p>
                        <p style="margin:0 0 12px;">
                            Votre demande d'accès au portail recruteur pour <strong>{{ $organization->name }}</strong> a été validée par notre équipe.
                        </p>
                        <p style="margin:0 0 12px;">
                            Vous pouvez désormais vous connecter avec l'adresse <strong>{{ $user->email }}</strong> et soumettre vos offres d'emploi pour publication après validation.
                        </p>
                        <p style="margin:0 0 20px;">
                            <a href="{{ $portalUrl }}" style="display:inline-block; background:#0f172a; color:#ffffff; text-decoration:none; padding:10px 16px; border-radius:6px; font-weight:600; margin-right:8px;">
                                Accéder au portail
                            </a>
                            <a href="{{ $loginUrl }}" style="display:inline-block; background:#e5e7eb; color:#111827; text-decoration:none; padding:10px 16px; border-radius:6px; font-weight:600;">
                                Se connecter
                            </a>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="background:#f8fafc; border-top:1px solid #e5e7eb; padding:16px 24px; text-align:center; color:#6b7280; font-size:12px;">
                        &copy; {{ date('Y') }} JBIS — Tous droits réservés.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
