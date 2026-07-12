<?php

declare(strict_types=1);

return [
    'week_start' => [
        'candidate' => [
            'title' => 'Bon début de semaine',
            'body' => 'Bonne semaine sur MyJob Best — de nouvelles opportunités vous attendent.',
        ],
        'staff' => [
            'title' => 'Bonne semaine d’équipe',
            'body' => 'Bonne semaine à l’équipe JBIS — priorisez les dossiers, les réunions et le suivi des tâches opérationnelles.',
        ],
    ],
    'weekend' => [
        'candidate' => [
            'title' => 'Bon week-end',
            'body' => 'Belle fin de semaine ! Reposez-vous bien — on se retrouve bientôt sur MyJob Best.',
        ],
        'staff' => [
            'title' => 'Bon week-end à l’équipe',
            'body' => 'Merci pour votre engagement cette semaine. Pensez au suivi des tâches ouvertes et passez un excellent week-end.',
        ],
    ],
    'birthday' => [
        'candidate' => [
            'title' => 'Joyeux anniversaire !',
            'body' => 'Bonjour :name, joyeux anniversaire de la part de toute l’équipe MyJob Best !',
        ],
        'staff' => [
            'title' => 'Joyeux anniversaire, collègue !',
            'body' => 'Bonjour :name, toute l’équipe JBIS vous souhaite un excellent anniversaire. Merci pour votre contribution au quotidien.',
        ],
    ],
    'birthday_followup' => [
        'candidate' => [
            'title' => 'Encore un joyeux anniversaire',
            'body' => 'Bonjour :name, toute l’équipe MyJob Best vous souhaite encore une merveilleuse année !',
        ],
        'staff' => [
            'title' => 'Encore un joyeux anniversaire',
            'body' => 'Bonjour :name, l’équipe JBIS vous souhaite encore une excellente année — merci d’être avec nous.',
        ],
    ],
    'staff_welcome' => [
        'title' => 'Bienvenue dans l’équipe JBIS',
        'body' => 'Bonjour :name, bienvenue parmi le staff de Job Best. Accédez aux réunions, tâches et outils opérationnels pour accompagner nos candidats.',
        'mail_intro' => 'Votre accès staff MyJob Best est maintenant actif. Vous pouvez rejoindre les réunions, suivre les tâches assignées et accompagner les dossiers candidats.',
        'mail_action' => 'Ouvrir l’espace staff',
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
    'candidacy_mail' => [
        'action' => 'Voir ma candidature',
    ],
    'application_submitted' => [
        'title' => 'Candidature enregistrée',
        'body' => 'Votre candidature :ref est en cours. Prochaine étape : :step.',
    ],
    'application_submitted_pending' => [
        'title' => 'Candidature en attente de validation',
        'body' => 'Votre candidature :ref a été reçue et est en attente de validation (documents ou revue staff).',
    ],
    'application_approved' => [
        'title' => 'Candidature approuvée',
        'body' => 'Félicitations ! Votre candidature :ref a été approuvée. Le parcours est terminé.',
    ],
    'application_rejected' => [
        'title' => 'Candidature refusée',
        'body' => 'Votre candidature :ref a été refusée. Motif : :reason',
        'no_reason' => 'non précisé',
    ],
    'application_cancelled' => [
        'title' => 'Candidature annulée',
        'body' => 'Votre candidature :ref a été annulée. Motif : :reason',
        'no_reason' => 'non précisé',
    ],
    'application_step_pending' => [
        'title' => 'Nouvelle étape à traiter',
        'body' => 'Candidature :ref — l’étape « :step » est maintenant à votre charge.',
    ],
    'application_payment_declared' => [
        'title' => 'Paiement déclaré',
        'body' => 'Candidature :ref — paiement de :amount XAF déclaré pour « :step ». En attente de confirmation.',
    ],
    'application_payment_confirmed' => [
        'title' => 'Paiement confirmé',
        'body' => 'Candidature :ref — paiement de :amount XAF confirmé pour « :step ».',
    ],
    'application_payment_waived' => [
        'title' => 'Paiement dispensé',
        'body' => 'Candidature :ref — le paiement de l’étape « :step » a été dispensé.',
    ],
    'application_payment_due' => [
        'title' => 'Paiement à venir',
        'body' => 'Candidature :ref — :amount XAF à régler pour « :step » avant le :due.',
    ],
    'application_payment_overdue' => [
        'title' => 'Paiement en retard',
        'body' => 'Candidature :ref — :amount XAF pour « :step » était dû le :due. Merci de régulariser.',
    ],
    'application_document_approved' => [
        'title' => 'Document approuvé',
        'body' => 'Candidature :ref — le document « :document » a été approuvé.',
    ],
    'application_document_rejected' => [
        'title' => 'Document refusé',
        'body' => 'Candidature :ref — le document « :document » a été refusé. :notes',
    ],
    'application_document_revision' => [
        'title' => 'Document à corriger',
        'body' => 'Candidature :ref — le document « :document » nécessite une révision. :notes',
    ],
    'application_document_reviewed' => [
        'no_notes' => 'Consultez votre dossier pour plus de détails.',
    ],
    'application_document_reminder' => [
        'title' => 'Documents manquants',
        'body' => 'Candidature :ref — :count document(s) requis manquent encore : :documents.',
    ],
    'application_document_step_reminder' => [
        'title' => 'Documents d’étape à soumettre',
        'body' => 'Candidature :ref — l’étape « :step » attend encore vos documents.',
    ],
    'application_protocol_accepted' => [
        'title' => 'Protocole accepté',
        'body' => 'Votre acceptation du protocole pour la candidature :ref a bien été enregistrée.',
    ],
];
