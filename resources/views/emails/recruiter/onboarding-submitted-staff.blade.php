<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Nouvelle demande recruteur</title>
</head>
<body style="font-family:Arial, sans-serif; color:#111827; line-height:1.6;">
<p>Une nouvelle demande d'accès au portail recruteur a été soumise.</p>
<ul>
    <li><strong>Entreprise :</strong> {{ $application->company_name }}</li>
    <li><strong>Contact :</strong> {{ $application->contact_name }} ({{ $application->contact_email }})</li>
    <li><strong>Téléphone :</strong> {{ $application->contact_phone ?? '—' }}</li>
    <li><strong>Statut :</strong> {{ $application->status?->value ?? $application->status }}</li>
</ul>
<p>
    <a href="{{ $reviewUrl }}">Analyser la demande</a>
</p>
</body>
</html>
