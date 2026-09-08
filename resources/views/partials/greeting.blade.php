@php
    $hour = (int) date('H');
    $greetings = match(true) {
        $hour >= 5 && $hour <= 7 => [
            'You\'re up early',
            'Morning already, huh',
            'Good morning',
            'Early start today',
            'Coffee first, then the tables?',
            'The early crowd has arrived',
            'Sun\'s barely up and you\'re here',
            'Morning',
            'Up before most people',
            'Well, look who\'s an early riser',
        ],
        $hour >= 8 && $hour <= 11 => [
            'Good morning',
            'Morning',
            'Hope your morning\'s off to a good start',
            'Back again',
            'Ready when you are',
            'Morning, what looks good today?',
            'Good to see you',
            'Let\'s see what today brings',
            'Here we go',
        ],
        $hour >= 12 && $hour <= 14 => [
            'Afternoon',
            'Taking a lunch break?',
            'Good afternoon',
            'Midday check-in',
            'Back for round two?',
            'Afternoon, how\'s it going',
            'Hope lunch was good',
            'What\'s the plan this afternoon?',
            'Hey there',
        ],
        $hour >= 15 && $hour <= 17 => [
            'Afternoon',
            'Getting into the swing of the day',
            'Good afternoon',
            'Almost evening',
            'Back again, I see',
            'Hope the day\'s treating you well',
            'How\'s the afternoon going',
            'Hey',
            'Good to have you back',
        ],
        $hour >= 18 && $hour <= 20 => [
            'Good evening',
            'Evening',
            'Winding down for the night?',
            'Hey, good evening',
            'Evening, how was your day',
            'Glad you\'re here',
            'Settling in for the evening?',
            'Evening, welcome back',
            'Hey there',
        ],
        $hour >= 21 && $hour <= 23 => [
            'Evening',
            'Still up, I see',
            'Good evening',
            'Late one tonight',
            'Hey, burning the midnight oil?',
            'Good to see you tonight',
            'Winding down or just getting started?',
            'Hey there',
            'Night owl mode, huh',
        ],
        default => [
            'You\'re up late',
            'Can\'t sleep either?',
            'Still here',
            'Late night session',
            'Hey, night owl',
            'Burning the midnight oil',
            'Quiet hours suit you',
            'Well, hello there',
            'Up at this hour, respect',
            'The night shift crew',
        ],
    };

    $lastGreeting = session('last_greeting');
    $candidates = count($greetings) > 1
        ? array_values(array_diff($greetings, [$lastGreeting]))
        : $greetings;

    // Seed so the pick is stable per user per hour-block, not per page load
    $seed = crc32((auth()->id() ?? 'guest') . '|' . date('Y-m-d-H'));
    mt_srand($seed);
    $greeting = $candidates[array_rand($candidates)];
    mt_srand(); // reseed randomly so nothing else in the request is affected

    session(['last_greeting' => $greeting]);
@endphp
{{ $greeting }}{{ $slot ?? '' }}
