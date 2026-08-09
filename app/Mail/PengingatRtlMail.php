<?php

namespace App\Mail;

use App\Models\SesiSupervisi;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sprint 10 backlog: "Integrasi SMTP ... Email terkirim saat reminder".
 * Dikirim via Mail::queue() dari KirimReminderRtlJob, bukan disinkronkan -
 * kegagalan pengiriman email memakai retry queue default Laravel sendiri,
 * terpisah dari notifikasi in-app yang sudah tersimpan lebih dulu.
 */
class PengingatRtlMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public readonly SesiSupervisi $sesi) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pengingat: Rencana Tindak Lanjut Belum Diisi',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.pengingat-rtl',
            with: [
                'namaGuru' => $this->sesi->guru->nama,
                'tanggalSesi' => $this->sesi->tanggal->format('d/m/Y'),
                'tautan' => route('app.sesi-supervisi.rtl', $this->sesi, absolute: true),
            ],
        );
    }
}
