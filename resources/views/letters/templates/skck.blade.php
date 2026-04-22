<div class="text-center mb-4">
    <h5 class="mb-1">SURAT PENGANTAR SKCK</h5>
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
            <td>Pekerjaan</td>
            <td>: {{ $letterRequest->resident->occupation ?? '-' }}</td>
        </tr>
    </tbody>
</table>

<p>
    Warga tersebut benar tinggal di wilayah kami dan memerlukan surat pengantar
    untuk pengurusan SKCK dengan tujuan <strong>{{ strtolower($letterRequest->purpose) }}</strong>.
</p>

<p>Demikian surat pengantar ini dibuat untuk melengkapi proses administrasi yang dibutuhkan.</p>

<div class="text-end mt-5">
    <p class="mb-1">Tanggal terbit: {{ $letterRequest->issued_at?->format('d/m/Y') }}</p>
    <p class="mb-5">Mengetahui,</p>
    <p class="mb-0"><strong>Pengurus RW</strong></p>
</div>
