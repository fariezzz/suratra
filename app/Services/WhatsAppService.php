<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\LetterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class WhatsAppService
{
    public function sendMessage(string $phone, string $message): bool
    {
        $baseUrl = $this->baseUrl();

        if (! $baseUrl || ! $this->isEnabled()) {
            return false;
        }

        try {
            $response = Http::timeout(15)
                ->post(rtrim($baseUrl, '/').'/send-message', [
                    'phone' => $phone,
                    'message' => $message,
                ]);

            return $response->successful() && ($response->json('ok') ?? true);
        } catch (\Throwable $e) {
            Log::warning('WhatsApp sendMessage gagal: '.$e->getMessage());

            return false;
        }
    }

    public function sendDocument(string $phone, string $message, string $filePath): bool
    {
        $baseUrl = $this->baseUrl();

        if (! $baseUrl || ! $this->isEnabled()) {
            return false;
        }

        $absolutePath = $this->resolvePath($filePath);

        if (! $absolutePath) {
            return false;
        }

        try {
            $response = Http::timeout(30)
                ->post(rtrim($baseUrl, '/').'/send-document', [
                    'phone' => $phone,
                    'message' => $message,
                    'filePath' => $absolutePath,
                ]);

            return $response->successful() && ($response->json('ok') ?? true);
        } catch (\Throwable $e) {
            Log::warning('WhatsApp sendDocument gagal: '.$e->getMessage());

            return false;
        }
    }

    public function notifyRTNewSubmission($pengajuan): bool
    {
        $pengajuan = $this->hydratePengajuan($pengajuan);

        if (! $pengajuan?->resident) {
            return false;
        }

        $message = sprintf(
            '📋 Pengajuan surat baru dari %s - %s. Silakan cek di aplikasi untuk meninjau.',
            $pengajuan->resident->name,
            $pengajuan->letter_type_label
        );

        $recipients = User::query()
            ->where('role', UserRole::RT->value)
            ->when($pengajuan->resident->rt, fn ($query) => $query->where('managed_rt', $pengajuan->resident->rt))
            ->with('resident')
            ->get();

        return $this->sendToUsers($recipients, $message);
    }

    public function notifyWargaDiterimaRT($pengajuan): bool
    {
        $pengajuan = $this->hydratePengajuan($pengajuan);
        $phone = $pengajuan?->resident?->phone;

        if (! $phone) {
            return false;
        }

        $message = sprintf(
            '✅ Pengajuan surat Anda (%s) telah diterima oleh RT dan diteruskan ke RW.',
            $pengajuan->letter_type_label
        );

        return $this->sendMessage($phone, $message);
    }

    public function notifyWargaDitolakRT($pengajuan, $alasan): bool
    {
        $pengajuan = $this->hydratePengajuan($pengajuan);
        $phone = $pengajuan?->resident?->phone;

        if (! $phone) {
            return false;
        }

        $message = sprintf(
            '❌ Pengajuan surat Anda (%s) ditolak oleh RT. Alasan: %s',
            $pengajuan->letter_type_label,
            $alasan ?: '-'
        );

        return $this->sendMessage($phone, $message);
    }

    public function notifyRWNewSubmission($pengajuan): bool
    {
        $pengajuan = $this->hydratePengajuan($pengajuan);

        if (! $pengajuan?->resident) {
            return false;
        }

        $message = sprintf(
            '📋 Ada pengajuan surat dari %s - %s yang perlu ditinjau. Silakan cek di aplikasi.',
            $pengajuan->resident->name,
            $pengajuan->letter_type_label
        );

        $recipients = User::query()
            ->where('role', UserRole::RW->value)
            ->with('resident')
            ->get();

        return $this->sendToUsers($recipients, $message);
    }

    public function notifyWargaDiterimaRW($pengajuan, $filePath): bool
    {
        $pengajuan = $this->hydratePengajuan($pengajuan);
        $phone = $pengajuan?->resident?->phone;

        if (! $phone) {
            return false;
        }

        $message = sprintf(
            "✅ Surat Anda (%s) telah ditandatangani dan siap diunduh.\n📄 Dokumen terlampir.",
            $pengajuan->letter_type_label
        );

        if (! $filePath) {
            return $this->sendMessage($phone, $message);
        }

        return $this->sendDocument($phone, $message, $filePath);
    }

    public function notifyWargaDitolakRW($pengajuan, $alasan): bool
    {
        $pengajuan = $this->hydratePengajuan($pengajuan);
        $phone = $pengajuan?->resident?->phone;

        if (! $phone) {
            return false;
        }

        $message = sprintf(
            '❌ Pengajuan surat Anda (%s) ditolak oleh RW. Alasan: %s',
            $pengajuan->letter_type_label,
            $alasan ?: '-'
        );

        return $this->sendMessage($phone, $message);
    }

    private function sendToUsers($users, string $message): bool
    {
        $sent = false;

        foreach ($users as $user) {
            $phone = $user->phone ?? $user->resident?->phone;

            if (! $phone) {
                continue;
            }

            $sent = $this->sendMessage($phone, $message) || $sent;
        }

        return $sent;
    }

    private function hydratePengajuan($pengajuan): ?LetterRequest
    {
        if (! $pengajuan instanceof LetterRequest) {
            return null;
        }

        $pengajuan->loadMissing('resident');

        return $pengajuan;
    }

    private function resolvePath(string $filePath): ?string
    {
        if ($filePath === '') {
            return null;
        }

        if (str_starts_with($filePath, '/') || preg_match('/^[A-Za-z]:\\\\/', $filePath)) {
            return $filePath;
        }

        if (Storage::disk('public')->exists($filePath)) {
            return Storage::disk('public')->path($filePath);
        }

        if (Storage::disk('local')->exists($filePath)) {
            return Storage::disk('local')->path($filePath);
        }

        return null;
    }

    private function isEnabled(): bool
    {
        return (bool) config('services.whatsapp.enabled', false);
    }

    private function baseUrl(): ?string
    {
        return config('services.whatsapp.base_url');
    }
}