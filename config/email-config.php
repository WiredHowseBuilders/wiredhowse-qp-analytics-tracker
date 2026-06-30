<?php
/**
 * Configuration file for Email Tracker
 * Location: config/email-config.php
 */

return [
    // Database configuration
    'database' => [
        'host'     => 'localhost',
        'dbname'   => 'dbddhoyjeeupcu',
        'username' => 'ugqr6zat75dx4',
        'password' => 'dr4G0nmaster11!'
    ],

    // IMAP configuration for YOUR Gmail account (koubre@gmail.com)
    // This checks YOUR email for messages FROM postmaster@defendsurviveprepare.com
    'imap' => [
        'host'     => 'imap.gmail.com',
        'port'     => 993,
        'username' => 'koubre@gmail.com',
        'password' => 'htdh kbzl cnie dwlk',
        'ssl'      => true,
        'folder'   => 'INBOX'
    ],

    // Email filtering
    'filtering' => [
        'sender_email'     => 'postmaster@defendsurviveprepare.com',
        'exclude_patterns' => ['TEST']
    ],

    // Default email collection settings
    'collection' => [
        'default_status'     => 'draft',
        'batch_size'         => 50,
        'auto_collect_unseen'=> true,
        'mark_as_read'       => false
    ],

    // Vendor tracking defaults
    'vendors' => [
        'default_vendor_id' => null,
        'auto_tag'          => false
    ],

    // Performance thresholds
    'thresholds' => [
        'good_quality_score'      => 7.0,
        'excellent_quality_score' => 8.5,
        'good_open_rate'          => 30.0,
        'good_conversion_rate'    => 2.0
    ]
];