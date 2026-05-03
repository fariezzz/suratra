<?php

namespace App\Services;

use App\Enums\LetterType;
use App\Models\LetterRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\TemplateProcessor;
use RuntimeException;

class LetterDocumentService
{
    public function generateDocx(LetterRequest $letterRequest): string
    {
        $letterRequest->load('resident');

        $templatePath = $this->resolveTemplatePath($letterRequest->letter_type);
        if (! file_exists($templatePath)) {
            throw new RuntimeException("Template DOCX tidak ditemukan: {$letterRequest->letter_type}");
        }

        $values = $this->templateValues($letterRequest);
        $baseFilename = $this->buildGeneratedFilenameBase($letterRequest);

        $templateProcessor = new TemplateProcessor($templatePath);
        foreach ($values as $placeholder => $value) {
            $templateProcessor->setValue($placeholder, $value ?? '');
        }

        $tempDocx = storage_path('app/temp/filled-' . $baseFilename . '.docx');
        @mkdir(dirname($tempDocx), 0755, true);
        $templateProcessor->saveAs($tempDocx);

        $filename = $baseFilename . '.docx';
        $publicPath = 'generated-letters/' . $filename;

        Storage::disk('public')->put($publicPath, file_get_contents($tempDocx));

        @unlink($tempDocx);

        return $publicPath;
    }

    public function generatePdfFromDocx(string $publicDocxPath): string
    {
        $fullPath = Storage::disk('public')->path($publicDocxPath);
        if (! file_exists($fullPath)) {
            throw new RuntimeException("File DOCX sumber tidak ditemukan: {$publicDocxPath}");
        }

        if (! class_exists('\Ilovepdf\Ilovepdf')) {
            throw new RuntimeException('iLovePDF SDK not installed. Run "composer require ilovepdf/ilovepdf-php"');
        }

        $publicKey = env('ILOVEPDF_PUBLIC_KEY');
        $secretKey = env('ILOVEPDF_SECRET_KEY');

        if (! $publicKey || ! $secretKey) {
            throw new RuntimeException('ILovePDF keys not configured. Set ILOVEPDF_PUBLIC_KEY and ILOVEPDF_SECRET_KEY in .env');
        }

        try {
            $ilovepdf = new \Ilovepdf\Ilovepdf($publicKey, $secretKey);
            $task = $ilovepdf->newTask('officepdf');
            $task->addFile($fullPath);
            $task->execute();

            $downloadDir = storage_path('app/temp/ilovepdf-' . pathinfo($publicDocxPath, PATHINFO_FILENAME));
            @mkdir($downloadDir, 0755, true);
            $task->download($downloadDir);

            $files = glob($downloadDir . DIRECTORY_SEPARATOR . '*.pdf');
            if (empty($files)) {
                throw new RuntimeException('iLovePDF did not return a converted PDF file.');
            }

            $convertedPdf = $files[0];
            $filename = pathinfo($publicDocxPath, PATHINFO_FILENAME) . '.pdf';
            $path = 'generated-letters/' . $filename;

            Storage::disk('public')->put($path, file_get_contents($convertedPdf));

            foreach ($files as $f) { @unlink($f); }
            @rmdir($downloadDir);

            return $path;
        } catch (\Throwable $e) {
            Log::error('iLovePDF conversion failed: ' . $e->getMessage());
            throw $e;
        }
    }

    private function templateValues(LetterRequest $letterRequest): array
    {
        $resident = $letterRequest->resident;
        $birthInfo = $this->formatBirthInfo($resident->birth_place, $resident->birth_date);
        
        return [
            'no_surat' => $letterRequest->letter_number ?? $letterRequest->reference_number,
            'RT' => str_pad($resident->rt, 3, '0', STR_PAD_LEFT),
            'nama' => $resident->name,
            'ttl' => $birthInfo,
            'jk' => $this->formatGender($resident->gender),
            'status_kawin' => $resident->status_kawin ?? '',
            'no_ktp' => $resident->nik,
            'agama' => $resident->agama ?? '',
            'pekerjaan' => $resident->occupation ?? '',
            'alamat' => $resident->address,
            'Keperluan' => $letterRequest->purpose ?? '',
        ];
    }

    private function formatGender(string $gender): string
    {
        return $gender === 'L' ? 'Laki-laki' : 'Perempuan';
    }

    private function formatBirthInfo(?string $birthPlace, $birthDate): string
    {
        if (!$birthPlace && !$birthDate) {
            return '';
        }
        
        $place = $birthPlace ?? '-';
        $date = '';
        
        if ($birthDate) {
            $date = Carbon::parse($birthDate)->isoFormat('D MMMM YYYY');
        }
        
        return "{$place}, {$date}";
    }

    private function resolveTemplatePath(string $letterType): string
    {
        $templates = [
            LetterType::GENERAL->value => ['letter-templates/surat_pengantar_umum.docx'],
            LetterType::DOMICILE->value => ['letter-templates/surat_keterangan_domisili.docx'],
            LetterType::SKCK->value => ['letter-templates/surat_pengantar_skck.docx'],
            LetterType::BUSINESS->value => ['letter-templates/surat_keterangan_usaha.docx'],
        ];

        $candidates = $templates[$letterType] ?? $templates['surat_pengantar_umum'];

        foreach ($candidates as $candidate) {
            $path = storage_path('app/' . $candidate);

            if (file_exists($path)) {
                return $path;
            }
        }

        return storage_path('app/' . $candidates[0]);
    }

    private function buildGeneratedFilenameBase(LetterRequest $letterRequest): string
    {
        $code = $this->letterCode($letterRequest->letter_type);
        $residentName = Str::upper(Str::slug($letterRequest->resident->name ?? 'warga', '_'));
        $date = Carbon::now()->format('Ymd');

        return implode('_', array_filter([$code, $residentName, $date]));
    }

    private function letterCode(string $letterType): string
    {
        return match ($letterType) {
            LetterType::DOMICILE->value => 'SKD',
            LetterType::SKCK->value => 'SKCK',
            LetterType::GENERAL->value => 'SPU',
            LetterType::BUSINESS->value => 'SKU',
            default => 'SURAT',
        };
    }
}
