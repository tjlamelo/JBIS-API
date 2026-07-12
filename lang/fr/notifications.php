<?php

declare(strict_types=1);

return [
    'week_start' => [
        'title' => 'Bon début de semaine',
        'body' => 'Bonne semaine sur MyJob Best — de nouvelles opportunités vous attendent.',
    ],
    'weekend' => [
        'title' => 'Bon week-end',
        'body' => 'Belle fin de semaine ! Reposez-vous bien — on se retrouve bientôt sur MyJob Best.',
    ],
    'birthday' => [
        'title' => 'Joyeux anniversaire !',
        'body' => 'Bonjour :name, joyeux anniversaire de la part de toute l’équipe MyJob Best !',
    ],
    'birthday_followup' => [
        'title' => 'Encore un joyeux anniversaire',
        'body' => 'Bonjour :name, toute l’équipe MyJob Best vous souhaite encore une merveilleuse année !',
    ],
    'holiday_mail' => [
        'closing' => 'Belle journée de fête de la part de toute l’équipe :brand.',
        'action' => 'Ouvrir MyJob Best',
    ],
    'offer_recommendation' => [
        'title_one' => 'Une offre correspond à votre profil',
        'title_many' => ':count offres correspondent à votre profil',
        'body' => 'Notre recommandation IA a identifié des opportunités adaptées à votre parcours. Consultez-les dès maintenant.',
    ],
    'ops_mail' => [
        'action' => 'Ouvrir le tableau opérations',
    ],
    'ops_meeting_invite' => [
        'title' => 'Invitation à une réunion',
        'body' => 'Vous êtes invité(e) à « :title » le :when (organisateur : :organizer).',
    ],
    'ops_meeting_present' => [
        'title' => 'Présence enregistrée',
        'body' => 'Vous êtes marqué(e) présent(e) à « :title ». Vous pouvez maintenant définir vos tâches issues de cette réunion.',
    ],
    'ops_task_assigned' => [
        'title' => 'Nouvelle tâche assignée',
        'body' => 'Tâche « :title » (échéance : :due) assignée par :by.',
    ],
    'ops_task_completed' => [
        'title' => 'Tâche terminée',
        'body' => ':by a marqué la tâche « :title » comme terminée.',
    ],
    'ops_task_overdue' => [
        'title' => 'Tâche en retard',
        'body' => 'La tâche « :title » est en retard (échéance : :due). Merci de la finaliser ou de mettre à jour son statut.',
    ],
    'ops_task_not_submitted' => [
        'title' => 'Suivi journalier manquant',
        'body' => 'Aucun suivi journalier n’a été saisi pour la tâche « :title » cette semaine.',
    ],
    'ops_weekly_personal' => [
        'title' => 'Récapitulatif hebdomadaire de vos tâches',
        'body' => 'Cette semaine : :done terminée(s), :open ouverte(s), :overdue en retard, :hours h saisies.',
    ],
    'ops_weekly_management' => [
        'title' => 'Récapitulatif management — tâches staff',
        'body' => 'Équipe (:staff) : :done terminée(s), :open ouverte(s), :overdue en retard. Consultez le tableau hebdomadaire.',
    ],
];
