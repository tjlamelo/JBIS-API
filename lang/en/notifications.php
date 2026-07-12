<?php

declare(strict_types=1);

return [
    'week_start' => [
        'title' => 'Have a great week',
        'body' => 'Have a great week on MyJob Best — new opportunities are waiting for you.',
    ],
    'weekend' => [
        'title' => 'Have a great weekend',
        'body' => 'Enjoy your weekend! Rest well — see you soon on MyJob Best.',
    ],
    'birthday' => [
        'title' => 'Happy birthday!',
        'body' => 'Hello :name, happy birthday from the entire MyJob Best team!',
    ],
    'birthday_followup' => [
        'title' => 'Happy birthday again',
        'body' => 'Hello :name, the MyJob Best team wishes you another wonderful year ahead!',
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
];
