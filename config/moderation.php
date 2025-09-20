<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Moderation Engine Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for the automated moderation engine.
    | You can adjust scoring rules, thresholds, and keywords here.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Scoring Thresholds
    |--------------------------------------------------------------------------
    */
    'thresholds' => [
        'safe' => 30,           // 0-30: Published instantly
        'review' => 70,         // 31-70: Published but flagged for review
        'auto_blocked' => 100,  // 71-100: Hidden immediately
    ],

    /*
    |--------------------------------------------------------------------------
    | Rule Scoring
    |--------------------------------------------------------------------------
    */
    'scoring' => [
        'profanity' => [
            'mild' => 20,
            'moderate' => 35,
            'severe' => 50,
        ],
        'hate_speech' => 50,
        'violence' => [
            'self_harm' => 60,
            'threats' => 50,
            'violence' => 40,
        ],
        'doxxing' => 40,
        'spam' => [
            'keywords' => 25,
            'excessive_links' => 30,
            'duplicate_content' => 25,
            'excessive_caps' => 15,
            'excessive_punctuation' => 10,
        ],
        'quality' => [
            'too_short' => 10,
            'mostly_caps' => 15,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Keyword Lists
    |--------------------------------------------------------------------------
    */
    'keywords' => [
        'profanity' => [
            'mild' => ['damn', 'hell', 'crap', 'piss'],
            'moderate' => ['shit', 'bitch', 'asshole', 'bastard'],
            'severe' => ['fuck', 'cunt', 'nigger', 'faggot', 'retard'],
        ],
        
        'hate_speech' => [
            'racial' => ['nigger', 'chink', 'spic', 'kike', 'towelhead'],
            'homophobic' => ['faggot', 'dyke', 'homo', 'queer'],
            'religious' => ['terrorist', 'jihad', 'infidel'],
            'general' => ['nazi', 'hitler', 'genocide', 'ethnic cleansing'],
        ],
        
        'violence' => [
            'self_harm' => ['suicide', 'kill myself', 'end it all', 'self harm', 'cutting', 'hurting myself'],
            'threats' => ['kill you', 'murder', 'die', 'shoot you', 'stab', 'bomb'],
            'violence' => ['rape', 'assault', 'beat up', 'torture', 'mutilate', 'gore'],
        ],
        
        'spam' => [
            'click here', 'buy now', 'free money', 'make money fast', 'lose weight fast',
            'viagra', 'casino', 'lottery', 'winner', 'congratulations you won',
            'limited time', 'act now', 'special offer', 'discount', 'promo code',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pattern Detection
    |--------------------------------------------------------------------------
    */
    'patterns' => [
        'doxxing' => [
            'phone' => '/\b(?:\+?1[-.\s]?)?\(?([0-9]{3})\)?[-.\s]?([0-9]{3})[-.\s]?([0-9]{4})\b/',
            'email' => '/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/',
            'address' => '/\b\d+\s+[A-Za-z\s]+(?:street|st|avenue|ave|road|rd|drive|dr|lane|ln|court|ct|circle|cir|way|blvd|boulevard)\b/i',
            'ssn' => '/\b\d{3}-?\d{2}-?\d{4}\b/',
            'credit_card' => '/\b(?:\d{4}[-\s]?){3}\d{4}\b/',
        ],
        
        'spam' => [
            'excessive_links' => '/https?:\/\/[^\s]+/i',
            'duplicate_text' => '/(.{10,})\1{2,}/',
            'excessive_caps' => '/[A-Z]{10,}/',
            'excessive_punctuation' => '/[!?]{5,}/',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Quality Checks
    |--------------------------------------------------------------------------
    */
    'quality' => [
        'minimum_words' => 5,
        'caps_percentage_threshold' => 70,
        'minimum_length_for_caps_check' => 20,
    ],

    /*
    |--------------------------------------------------------------------------
    | Spam Detection Settings
    |--------------------------------------------------------------------------
    */
    'spam' => [
        'max_links' => 2,
        'duplicate_check_days' => 7,
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    */
    'features' => [
        'profanity_detection' => env('MODERATION_PROFANITY_DETECTION', true),
        'hate_speech_detection' => env('MODERATION_HATE_SPEECH_DETECTION', true),
        'violence_detection' => env('MODERATION_VIOLENCE_DETECTION', true),
        'doxxing_detection' => env('MODERATION_DOXXING_DETECTION', true),
        'spam_detection' => env('MODERATION_SPAM_DETECTION', true),
        'quality_checks' => env('MODERATION_QUALITY_CHECKS', true),
        'duplicate_detection' => env('MODERATION_DUPLICATE_DETECTION', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    */
    'logging' => [
        'enabled' => env('MODERATION_LOGGING', true),
        'log_safe_content' => env('MODERATION_LOG_SAFE', false),
        'log_flagged_content' => env('MODERATION_LOG_FLAGGED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Command Settings
    |--------------------------------------------------------------------------
    */
    'command' => [
        'default_batch_size' => 50,
        'max_batch_size' => 500,
        'timeout_minutes' => 30,
        'remoderation_interval_hours' => 24,
    ],
];