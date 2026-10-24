<?php

/*
 * Invoice formats offered in Settings. A branch that's never touched its
 * setting (or picked one the view for which no longer exists) falls back to
 * "default" below. The key is stored on branches.invoice_format and
 * invoices.invoice_format; "view" is the Blade view rendered for HTML,
 * print and PDF.
 */
return [
    'default' => 'gst-tax-invoice',

    'formats' => [
        'gst-tax-invoice' => [
            'name' => 'GST Tax Invoice',
            'description' => 'Full GST format with HSN summary and signature.',
            'view' => 'invoices.formats.gst-tax-invoice',
            'accent' => '#6A00D9',
            'thumbnail' => 'images/invoice-formats/gst-tax-invoice.jpg',
        ],
        'saffron' => [
            'name' => 'Saffron',
            'description' => 'Warm orange header with a maroon balance box.',
            'view' => 'invoices.formats.saffron',
            'accent' => '#EA6A0C',
            'thumbnail' => 'images/invoice-formats/saffron.jpg',
        ],
        'teal-minimal' => [
            'name' => 'Teal Minimal',
            'description' => 'Modern and clean with a teal side stripe.',
            'view' => 'invoices.formats.teal-minimal',
            'accent' => '#0F766E',
            'thumbnail' => 'images/invoice-formats/teal-minimal.jpg',
        ],
        'sunset' => [
            'name' => 'Sunset',
            'description' => 'Bright coral design with a PAID or DUE stamp.',
            'view' => 'invoices.formats.sunset',
            'accent' => '#F2545B',
            'thumbnail' => 'images/invoice-formats/sunset.jpg',
        ],
        'bill-book' => [
            'name' => 'Bill Book',
            'description' => 'Traditional ruled cash and credit memo look.',
            'view' => 'invoices.formats.bill-book',
            'accent' => '#8B1E1E',
            'thumbnail' => 'images/invoice-formats/bill-book.jpg',
        ],
    ],
];
