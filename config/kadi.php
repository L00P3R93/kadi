<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Legal consent
    |--------------------------------------------------------------------------
    |
    | Users must confirm they are of legal age and accept the current terms.
    | We store only the confirmation (a timestamp) and the terms version they
    | accepted — never a date of birth. Bump `terms_version` whenever the
    | Terms or Privacy Policy change materially to re-prompt users at their
    | next login.
    |
    */

    'min_age' => 18,

    'terms_version' => '2026-09-19',

];
