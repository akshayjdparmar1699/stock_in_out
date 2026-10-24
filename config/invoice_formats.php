<?php

/*
 * Invoice formats offered in Settings, in addition to the original design
 * (which has no entry here — it's what every branch uses until it picks one
 * of these). The key is stored on branches.invoice_format and invoices.invoice_format;
 * "view" is the Blade view rendered for HTML, print and PDF.
 */
return [
    'formats' => [
        'gst-tax-invoice' => [
            'name' => 'GST Tax Invoice',
            'description' => 'Full GST format with HSN summary and signature.',
            'view' => 'invoices.formats.gst-tax-invoice',
            'accent' => '#6A00D9',
        ],
        'saffron' => [
            'name' => 'Saffron',
            'description' => 'Warm orange header with a maroon balance box.',
            'view' => 'invoices.formats.saffron',
            'accent' => '#EA6A0C',
        ],
        'teal-minimal' => [
            'name' => 'Teal Minimal',
            'description' => 'Modern and clean with a teal side stripe.',
            'view' => 'invoices.formats.teal-minimal',
            'accent' => '#0F766E',
        ],
        'sunset' => [
            'name' => 'Sunset',
            'description' => 'Bright coral design with a PAID or DUE stamp.',
            'view' => 'invoices.formats.sunset',
            'accent' => '#F2545B',
        ],
        'bill-book' => [
            'name' => 'Bill Book',
            'description' => 'Traditional ruled cash and credit memo look.',
            'view' => 'invoices.formats.bill-book',
            'accent' => '#8B1E1E',
        ],
    ],
];
