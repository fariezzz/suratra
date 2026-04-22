<div class="text-center mb-4">
    <h5 class="mb-1">SURAT PENGANTAR UMUM</h5>
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
            <td>RT/RW</td>
            <td>: {{ $letterRequest->resident->rt }}/{{ $letterRequest->resident->rw }}</td>
        </tr>
    </tbody>
</table>

<p>
    Benar merupakan warga di lingkungan kami dan surat pengantar umum ini diterbitkan
    untuk keperluan <strong>{{ strtolower($letterRequest->purpose) }}</strong>.
</p>

<p>Demikian surat ini dibuat agar dapat dipergunakan sebagaimana mestinya.</p>

<div class="text-end mt-5">
    <p class="mb-1">Tanggal terbit: {{ $letterRequest->issued_at?->format('d/m/Y') }}</p>
    <p class="mb-5">Mengetahui,</p>
    <p class="mb-0"><strong>Pengurus RW</strong></p>
</div>
