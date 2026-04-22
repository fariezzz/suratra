@extends('layouts.app')

@php
    use App\Enums\LetterRequestStatus;
@endphp

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h1 class="h4 mb-1">Layanan Persuratan RT/RW</h1>
            <p class="text-muted mb-0">
                @if ($isWarga)
                    Ajukan surat Anda, unggah dokumen persyaratan, lalu pantau statusnya.
                @elseif ($isRt)
                    Verifikasi awal pengajuan warga sebelum diteruskan ke RW.
                @else
                    Berikan keputusan akhir dan sahkan surat setelah verifikasi RT.
                @endif
            </p>
        </div>
    </div>

    <div class="row g-3">
        @if ($isWarga)
            <div class="col-lg-4">
                <div class="card card-soft">
                    <div class="card-header bg-white">
                        <strong>Form Pengajuan Surat</strong>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('letters.store') }}" method="POST" enctype="multipart/form-data" class="d-flex flex-column gap-3" id="letterForm">
                            @csrf

                            <div>
                                <label class="form-label" for="letter_type">Jenis Surat</label>
                                <select class="form-select" name="letter_type" id="letter_type" required>
                                    <option value="">-- Pilih Jenis Surat --</option>
                                    @foreach ($letterTypes as $value => $label)
                                        <option value="{{ $value }}" @selected(old('letter_type') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="form-label" for="purpose">Keperluan</label>
                                <textarea class="form-control" id="purpose" name="purpose" rows="3" placeholder="Contoh: Syarat administrasi kerja" required>{{ old('purpose') }}</textarea>
                            </div>

                            <div>
                                <label class="form-label">Dokumen Persyaratan</label>
                                <div id="documentRequirements" class="d-flex flex-column gap-2">
                                    <div class="form-doc-card text-muted small">Pilih jenis surat untuk menampilkan dokumen yang wajib diunggah.</div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-send-check me-1"></i>Kirim Pengajuan
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
        @else
            <div class="col-12">
        @endif
            <div class="card card-soft">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <strong>Daftar Pengajuan</strong>
                    <span class="small text-muted">Alur: Warga -> RT -> RW -> Selesai</span>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Referensi</th>
                                <th>Pemohon</th>
                                <th>Jenis Surat</th>
                                <th>Dokumen</th>
                                <th>Status</th>
                                <th>Catatan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($letterRequests as $requestItem)
                                <tr>
                                    <td>{{ $requestItem->reference_number }}</td>
                                    <td>
                                        <div>{{ $requestItem->resident->name }}</div>
                                        <small class="text-muted">NIK {{ $requestItem->resident->nik }}</small>
                                    </td>
                                    <td>{{ $requestItem->letter_type_label }}</td>
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            @forelse ($requestItem->documents ?? [] as $doc)
                                                <a href="{{ route('letters.document', [$requestItem, $doc['key']]) }}" class="small text-decoration-none">
                                                    <i class="bi bi-paperclip me-1"></i>{{ $doc['label'] }}
                                                </a>
                                            @empty
                                                <span class="text-muted small">-</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge status-badge {{ $requestItem->status_badge_class }}">{{ $requestItem->status_label }}</span>
                                    </td>
                                    <td class="small">
                                        @if ($requestItem->rt_notes)
                                            <div><strong>RT:</strong> {{ $requestItem->rt_notes }}</div>
                                        @endif
                                        @if ($requestItem->rw_notes)
                                            <div><strong>RW:</strong> {{ $requestItem->rw_notes }}</div>
                                        @endif
                                        @if (! $requestItem->rt_notes && ! $requestItem->rw_notes)
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($isRt && $requestItem->status === LetterRequestStatus::PENDING_RT->value)
                                            <div class="d-flex flex-column gap-2">
                                                <form method="POST" action="{{ route('letters.rt-decision', $requestItem) }}">
                                                    @csrf
                                                    <input type="hidden" name="decision" value="approve">
                                                    <button type="submit" class="btn btn-sm btn-outline-success w-100">Setujui RT</button>
                                                </form>
                                                <form method="POST" action="{{ route('letters.rt-decision', $requestItem) }}">
                                                    @csrf
                                                    <input type="hidden" name="decision" value="reject">
                                                    <textarea name="notes" class="form-control form-control-sm mb-1" rows="2" placeholder="Catatan revisi RT" required></textarea>
                                                    <button type="submit" class="btn btn-sm btn-outline-danger w-100">Tolak RT</button>
                                                </form>
                                            </div>
                                        @elseif ($isRw && $requestItem->status === LetterRequestStatus::PENDING_RW->value)
                                            <div class="d-flex flex-column gap-2">
                                                <form method="POST" action="{{ route('letters.rw-decision', $requestItem) }}">
                                                    @csrf
                                                    <input type="hidden" name="decision" value="approve">
                                                    <button type="submit" class="btn btn-sm btn-outline-success w-100">Setujui RW</button>
                                                </form>
                                                <form method="POST" action="{{ route('letters.rw-decision', $requestItem) }}">
                                                    @csrf
                                                    <input type="hidden" name="decision" value="reject">
                                                    <textarea name="notes" class="form-control form-control-sm mb-1" rows="2" placeholder="Catatan penolakan RW" required></textarea>
                                                    <button type="submit" class="btn btn-sm btn-outline-danger w-100">Tolak RW</button>
                                                </form>
                                            </div>
                                        @elseif ($requestItem->status === LetterRequestStatus::COMPLETED->value)
                                            <a href="{{ route('letters.show', $requestItem) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-file-earmark-text me-1"></i>Lihat Surat
                                            </a>
                                        @else
                                            <span class="text-muted small">Menunggu proses</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">Belum ada pengajuan surat.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white">
                    {{ $letterRequests->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection

@if ($isWarga)
    @push('scripts')
        <script>
            const requirementsMap = @json($requirementsMap);
            const oldFiles = @json(array_keys(old('documents', [])));
            const typeSelect = document.getElementById('letter_type');
            const requirementsContainer = document.getElementById('documentRequirements');

            function createRequirementInput(key, label) {
                const wrapper = document.createElement('div');
                wrapper.className = 'form-doc-card';

                const labelEl = document.createElement('label');
                labelEl.className = 'form-label mb-1';
                labelEl.setAttribute('for', `doc_${key}`);
                labelEl.textContent = label;

                const input = document.createElement('input');
                input.className = 'form-control form-control-sm';
                input.type = 'file';
                input.id = `doc_${key}`;
                input.name = `documents[${key}]`;
                input.accept = '.pdf,.jpg,.jpeg,.png';
                input.required = true;

                const hint = document.createElement('small');
                hint.className = 'text-muted';
                hint.textContent = 'Format: PDF/JPG/PNG (maksimal 2MB)';

                if (oldFiles.includes(key)) {
                    const info = document.createElement('div');
                    info.className = 'small text-success mt-1';
                    info.textContent = 'Dokumen sebelumnya sudah dipilih, silakan unggah ulang.';
                    wrapper.appendChild(info);
                }

                wrapper.appendChild(labelEl);
                wrapper.appendChild(input);
                wrapper.appendChild(hint);

                return wrapper;
            }

            function renderRequirements(letterType) {
                requirementsContainer.innerHTML = '';
                const requirements = requirementsMap[letterType];

                if (!requirements) {
                    requirementsContainer.innerHTML = '<div class="form-doc-card text-muted small">Pilih jenis surat untuk menampilkan dokumen yang wajib diunggah.</div>';
                    return;
                }

                Object.entries(requirements).forEach(([key, label]) => {
                    requirementsContainer.appendChild(createRequirementInput(key, label));
                });
            }

            typeSelect.addEventListener('change', event => {
                renderRequirements(event.target.value);
            });

            if (typeSelect.value) {
                renderRequirements(typeSelect.value);
            }
        </script>
    @endpush
@endif
