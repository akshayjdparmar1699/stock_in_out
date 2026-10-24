<?php

namespace App\Support;

/**
 * Computes every value an invoice format needs (tax split, totals, words, logo),
 * so each Blade format only handles layout and all formats print identical numbers.
 *
 * Inputs may be Eloquent models, arrays or plain objects; fields are read with data_get().
 */
class InvoicePresenter
{
    public bool $isGst;
    public bool $isInterState;
    public array $rows = [];
    public array $hsnSummary = [];
    public float $subtotal = 0.0;
    public float $taxTotal = 0.0;
    public float $cgst = 0.0;
    public float $sgst = 0.0;
    public float $discount;
    public float $transport;
    public float $roundOff;
    public float $grandTotal;
    public float $received;
    public float $previousDue;
    public float $balanceDue;
    public string $amountInWords;
    public string $dateText;
    public string $statusLabel;
    public ?string $logoSrc = null;
    public string $initials;
    public string $sellerState;
    public string $supplyState;

    public function __construct(
        public mixed $business,
        public mixed $invoice,
        public mixed $customer,
        iterable $items,
    ) {
        $this->isGst = (bool) $this->inv('is_gst', false);
        $ratesIncludeGst = (bool) $this->inv('rates_include_gst', false);
        $this->sellerState = (string) $this->biz('state_code', '');
        $this->supplyState = (string) $this->inv('place_of_supply_code', $this->sellerState);
        $this->isInterState = $this->isGst
            && $this->sellerState !== ''
            && $this->supplyState !== ''
            && $this->sellerState !== $this->supplyState;

        foreach ($items as $item) {
            $qty = (float) data_get($item, 'qty', 0);
            $rate = (float) data_get($item, 'rate', 0);
            $gstRate = $this->isGst ? (float) data_get($item, 'gst_rate', 0) : 0.0;
            $line = $qty * $rate;

            // Inclusive rates: back out the taxable value so the billed amount stays unchanged.
            $taxable = ($this->isGst && $ratesIncludeGst && $gstRate > 0) ? $line / (1 + $gstRate / 100) : $line;
            $taxable = round($taxable, 2);
            $tax = $this->isGst ? round($taxable * $gstRate / 100, 2) : 0.0;
            $half = round($tax / 2, 2);

            $this->rows[] = [
                'name' => data_get($item, 'name'),
                'hsn' => data_get($item, 'hsn'),
                'qty' => self::qty($qty),
                'unit' => data_get($item, 'unit'),
                'rate' => $rate,
                'gst_rate' => $gstRate,
                'gst_label' => self::pct($gstRate),
                'half_label' => self::pct($gstRate / 2),
                'taxable' => $taxable,
                'cgst' => $half,
                'sgst' => $tax - $half,
                'tax' => $tax,
                'total' => $taxable + $tax,
            ];

            $this->subtotal += $taxable;
            $this->taxTotal += $tax;
            $this->cgst += $half;
            $this->sgst += $tax - $half;

            if ($this->isGst) {
                $key = (string) (data_get($item, 'hsn') ?: '-');
                $this->hsnSummary[$key] ??= ['taxable' => 0.0, 'tax' => 0.0];
                $this->hsnSummary[$key]['taxable'] += $taxable;
                $this->hsnSummary[$key]['tax'] += $tax;
            }
        }

        $this->discount = (float) $this->inv('discount', 0);
        $this->transport = (float) $this->inv('transport', 0);
        $raw = $this->subtotal + $this->taxTotal - $this->discount + $this->transport;
        $this->grandTotal = round($raw);
        $this->roundOff = $this->grandTotal - $raw;
        $this->received = (float) $this->inv('received', 0);
        $this->previousDue = (float) $this->inv('previous_due', 0);
        $this->balanceDue = $this->previousDue + $this->grandTotal - $this->received;

        $this->amountInWords = NumberToWords::convert((int) $this->grandTotal) . ' Rupees Only';

        $date = $this->inv('date');
        $this->dateText = $date instanceof \DateTimeInterface ? $date->format('d M Y') : (string) $date;

        $this->statusLabel = (string) ($this->inv('status') ?: match (true) {
            $this->received >= $this->grandTotal => 'Paid',
            $this->received > 0 => 'Partly paid',
            default => 'Unpaid',
        });

        // Base64 keeps dompdf independent of remote access and chroot settings.
        $logoPath = $this->biz('logo_path');
        if ($logoPath && is_file($logoPath)) {
            $mime = mime_content_type($logoPath) ?: 'image/png';
            $this->logoSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
        }

        $words = array_filter(explode(' ', (string) $this->biz('name', '')));
        $this->initials = implode('', array_map(
            fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)),
            array_slice(array_values($words), 0, 2)
        ));
    }

    public function biz(string $key, mixed $default = null): mixed
    {
        return data_get($this->business, $key, $default);
    }

    public function inv(string $key, mixed $default = null): mixed
    {
        return data_get($this->invoice, $key, $default);
    }

    public function cust(string $key, mixed $default = null): mixed
    {
        return data_get($this->customer, $key, $default);
    }

    public function title(): string
    {
        return $this->isGst ? 'Tax Invoice' : 'Invoice';
    }

    public function isPaid(): bool
    {
        return $this->balanceDue <= 0.004;
    }

    public static function money(float|int|string|null $v): string
    {
        return '₹' . number_format((float) $v, 2);
    }

    public static function num(float|int|string|null $v): string
    {
        return number_format((float) $v, 2);
    }

    public static function qty(float $v): string
    {
        return rtrim(rtrim(number_format($v, 3, '.', ''), '0'), '.');
    }

    public static function pct(float $v): string
    {
        return rtrim(rtrim(number_format($v, 2, '.', ''), '0'), '.') . '%';
    }

}
