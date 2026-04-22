<div class="text-center mb-4">
    <h5 class="mb-1">SURAT PENGANTAR DOMISILI</h5>
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
            <td>Tempat/Tgl Lahir</td>
            <td>: {{ $letterRequest->resident->birth_place ?? '-' }}, {{ $letterRequest->resident->birth_date?->format('d/m/Y') ?? '-' }}</td>
        </tr>
        <tr>
            <td>Alamat Domisili</td>
            <td>: {{ $letterRequest->resident->address }}</td>
        </tr>
        <tr>
            <td>RT/RW</td>
            <td>: {{ $letterRequest->resident->rt }}/{{ $letterRequest->resident->rw }}</td>
        </tr>
    </tbody>
</table>

<p>
    Berdasarkan data administrasi lingkungan, yang bersangkutan benar berdomisili di alamat tersebut.
    Surat ini diberikan untuk keperluan <strong>{{ strtolower($letterRequest->purpose) }}</strong>.
</p>

<p>Demikian surat pengantar domisili ini dibuat untuk dipergunakan sebagaimana mestinya.</p>

<div class="text-end mt-5">
    <p class="mb-1">Tanggal terbit: {{ $letterRequest->issued_at?->format('d/m/Y') }}</p>
    <p class="mb-5">Mengetahui,</p>
    <p class="mb-0"><strong>Pengurus RW</strong></p>
</div>
