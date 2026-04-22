<div class="text-center mb-4">
    <h5 class="mb-1">SURAT KETERANGAN USAHA</h5>
    <p class="mb-0">Nomor: {{ $letterRequest->letter_number }}</p>
</div>

<p>Yang bertanda tangan di bawah ini, Pengurus RW setempat, menerangkan bahwa:</p>

<table class="table table-borderless table-sm w-auto">
    <tbody>
        <tr>
            <td>Nama</td>
            <td>: {{ $letterRequest->resident->name }}</td>
        </tr>
        <tr>
            <td>NIK</td>
            <td>: {{ $letterRequest->resident->nik }}</td>
        </tr>
        <tr>
            <td>Alamat</td>
            <td>: {{ $letterRequest->resident->address }}</td>
        </tr>
        <tr>
            <td>Jenis Pekerjaan</td>
            <td>: {{ $letterRequest->resident->occupation ?? '-' }}</td>
        </tr>
    </tbody>
</table>

<p>
    Berdasarkan keterangan lingkungan, yang bersangkutan benar memiliki/menjalankan kegiatan usaha
    di wilayah kami. Surat ini dipergunakan untuk keperluan <strong>{{ strtolower($letterRequest->purpose) }}</strong>.
</p>

<p>Demikian surat keterangan usaha ini dibuat agar dapat dipergunakan sebagaimana mestinya.</p>

<div class="text-end mt-5">
    <p class="mb-1">Tanggal terbit: {{ $letterRequest->issued_at?->format('d/m/Y') }}</p>
    <p class="mb-5">Mengetahui,</p>
    <p class="mb-0"><strong>Pengurus RW</strong></p>
</div>
