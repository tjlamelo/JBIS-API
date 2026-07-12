<?php

declare(strict_types=1);

return [
    'week_start' => [
        'candidate' => [
            'title' => 'Have a great week',
            'body' => 'Have a great week on MyJob Best — new opportunities are waiting for you.',
        ],
        'staff' => [
            'title' => 'Have a productive week, team',
            'body' => 'Have a great week with the JBIS team — prioritize dossiers, meetings and operational task follow-up.',
        ],
    ],
    'weekend' => [
        'candidate' => [
            'title' => 'Have a great weekend',
            'body' => 'Enjoy your weekend! Rest well — see you soon on MyJob Best.',
        ],
        'staff' => [
            'title' => 'Have a great weekend, team',
            'body' => 'Thank you for your commitment this week. Review open tasks if needed, then enjoy your weekend.',
        ],
    ],
    'birthday' => [
        'candidate' => [
            'title' => 'Happy birthday!',
            'body' => 'Hello :name, happy birthday from the entire MyJob Best team!',
        ],
        'staff' => [
            'title' => 'Happy birthday, teammate!',
            'body' => 'Hello :name, the JBIS team wishes you a wonderful birthday. Thank you for your daily contribution.',
        ],
    ],
    'birthday_followup' => [
        'candidate' => [
            'title' => 'Happy birthday again',
            'body' => 'Hello :name, the MyJob Best team wishes you another wonderful year ahead!',
        ],
        'staff' => [
            'title' => 'Happy birthday again',
            'body' => 'Hello :name, the JBIS team wishes you another great year — thank you for being with us.',
        ],
    ],
    'staff_welcome' => [
        'title' => 'Welcome to the JBIS team',
        'body' => 'Hello :name, welcome to the MyJob Best staff. Access meetings, tasks and operational tools to support our candidates.',
        'mail_intro' => 'Your MyJob Best staff access is now active. You can join meetings, follow assigned tasks and support candidate dossiers.',
        'mail_action' => 'Open staff workspace',
    ],
    'holiday_mail' => [
        'closing' => 'Have a wonderful holiday from the entire :brand team.',
        'action' => 'Open MyJob Best',
    ],
    'offer_recommendation' => [
        'title_one' => 'One offer matches your profile',
        'title_many' => ':count offers match your profile',
        'body' => 'Our AI recommendation found opportunities that fit your background. Check them out now.',
    ],
    'ops_mail' => [
        'action' => 'Open operations board',
    ],
    'ops_meeting_invite' => [
        'title' => 'Meeting invitation',
        'body' => 'You are invited to “:title” on :when (organizer: :organizer).',
    ],
    'ops_meeting_present' => [
        'title' => 'Attendance recorded',
        'body' => 'You are marked present at “:title”. You can now define your tasks from this meeting.',
    ],
    'ops_task_assigned' => [
        'title' => 'New task assigned',
        'body' => 'Task “:title” (due: :due) assigned by :by.',
    ],
    'ops_task_completed' => [
        'title' => 'Task completed',
        'body' => ':by marked “:title” as completed.',
    ],
    'ops_task_overdue' => [
        'title' => 'Overdue task',
        'body' => 'Task “:title” is overdue (due: :due). Please complete it or update its status.',
    ],
    'ops_task_not_submitted' => [
        'title' => 'Missing daily follow-up',
        'body' => 'No daily follow-up was logged for “:title” this week.',
    ],
    'ops_weekly_personal' => [
        'title' => 'Your weekly task recap',
        'body' => 'This week: :done done, :open open, :overdue overdue, :hours h logged.',
    ],
    'ops_weekly_management' => [
        'title' => 'Management weekly task recap',
        'body' => 'Team (:staff): :done done, :open open, :overdue overdue. Open the weekly board.',
    ],
    'candidacy_mail' => [
        'action' => 'View my application',
    ],
    'application_submitted' => [
        'title' => 'Application submitted',
        'body' => 'Your application :ref is in progress. Next step: :step.',
    ],
    'application_submitted_pending' => [
        'title' => 'Application pending validation',
        'body' => 'Your application :ref was received and is pending validation (documents or staff review).',
    ],
    'application_approved' => [
        'title' => 'Application approved',
        'body' => 'Congratulations! Your application :ref has been approved. The process is complete.',
    ],
    'application_rejected' => [
        'title' => 'Application rejected',
        'body' => 'Your application :ref was rejected. Reason: :reason',
        'no_reason' => 'not specified',
    ],
    'application_cancelled' => [
        'title' => 'Application cancelled',
        'body' => 'Your application :ref was cancelled. Reason: :reason',
        'no_reason' => 'not specified',
    ],
    'application_step_pending' => [
        'title' => 'New step to complete',
        'body' => 'Application :ref — step “:step” is now ready for you.',
    ],
    'application_payment_declared' => [
        'title' => 'Payment declared',
        'body' => 'Application :ref — payment of :amount XAF declared for “:step”. Awaiting confirmation.',
    ],
    'application_payment_confirmed' => [
        'title' => 'Payment confirmed',
        'body' => 'Application :ref — payment of :amount XAF confirmed for “:step”.',
    ],
    'application_payment_waived' => [
        'title' => 'Payment waived',
        'body' => 'Application :ref — payment for step “:step” has been waived.',
    ],
    'application_payment_due' => [
        'title' => 'Upcoming payment',
        'body' => 'Application :ref — :amount XAF due for “:step” before :due.',
    ],
    'application_payment_overdue' => [
        'title' => 'Overdue payment',
        'body' => 'Application :ref — :amount XAF for “:step” was due on :due. Please settle it.',
    ],
    'application_document_approved' => [
        'title' => 'Document approved',
        'body' => 'Application :ref — document “:document” was approved.',
    ],
    'application_document_rejected' => [
        'title' => 'Document rejected',
        'body' => 'Application :ref — document “:document” was rejected. :notes',
    ],
    'application_document_revision' => [
        'title' => 'Document needs revision',
        'body' => 'Application :ref — document “:document” requires revision. :notes',
    ],
    'application_document_reviewed' => [
        'no_notes' => 'Check your application for more details.',
    ],
    'application_document_reminder' => [
        'title' => 'Missing documents',
        'body' => 'Application :ref — :count required document(s) are still missing: :documents.',
    ],
    'application_document_step_reminder' => [
        'title' => 'Step documents pending',
        'body' => 'Application :ref — step “:step” is still waiting for your documents.',
    ],
    'application_protocol_accepted' => [
        'title' => 'Protocol accepted',
        'body' => 'Your protocol acceptance for application :ref has been recorded.',
    ],
];
