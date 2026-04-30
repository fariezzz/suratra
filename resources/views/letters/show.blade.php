@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <a href="{{ route('letters.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
        <div class="btn-group">
            @if ($letterRequest->generated_pdf_path)
                <a href="{{ route('letters.pdf', $letterRequest) }}" target="_blank" class="btn btn-success">
                    <i class="bi bi-filetype-pdf me-1"></i>Buka PDF
                </a>
            @endif
            @if ($letterRequest->generated_docx_path)
                <a href="{{ route('letters.docx', $letterRequest) }}" class="btn btn-primary">
                    <i class="bi bi-file-earmark-word me-1"></i>Unduh DOCX
                </a>
            @endif
        </div>
    </div>

    <div class="letter-sheet p-3 p-lg-4 bg-light rounded-3 border no-print">
        @if ($letterRequest->generated_pdf_path && Storage::disk('public')->exists($letterRequest->generated_pdf_path))
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="text-muted small" id="pdf-status">Memuat preview...</div>
            </div>

            <div id="pdf-preview" class="d-grid gap-3"></div>
        @else
            <div class="alert alert-warning mb-0">Pratinjau PDF tidak tersedia.</div>
        @endif
    </div>
@endsection

@push('scripts')
    @if ($letterRequest->generated_pdf_path && Storage::disk('public')->exists($letterRequest->generated_pdf_path))
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
        <script>
            (function() {
                const pdfUrl = @json(route('letters.pdf', $letterRequest));
                const container = document.getElementById('pdf-preview');
                const statusEl = document.getElementById('pdf-status');

                if (!window.pdfjsLib || !container) {
                    if (statusEl) statusEl.textContent = 'PDF.js gagal dimuat.';
                    return;
                }

                pdfjsLib.GlobalWorkerOptions.workerSrc =
                    'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

                const renderPage = async (page) => {
                    const containerWidth = container.clientWidth;

                    const viewport = page.getViewport({
                        scale: 1
                    });

                    const scale = containerWidth / viewport.width;
                    const outputScale = Math.max(window.devicePixelRatio || 1, 2);

                    const scaledViewport = page.getViewport({
                        scale
                    });

                    const canvas = document.createElement('canvas');
                    const context = canvas.getContext('2d');

                    canvas.width = Math.floor(scaledViewport.width * outputScale);
                    canvas.height = Math.floor(scaledViewport.height * outputScale);

                    canvas.style.width = `${scaledViewport.width}px`;
                    canvas.style.height = `${scaledViewport.height}px`;

                    context.setTransform(outputScale, 0, 0, outputScale, 0, 0);

                    canvas.className = 'bg-white border rounded-3 shadow-sm mb-3';

                    container.appendChild(canvas);

                    await page.render({
                        canvasContext: context,
                        viewport: scaledViewport,
                    }).promise;
                };

                const loadPdf = async () => {
                    try {
                        const loadingTask = pdfjsLib.getDocument(pdfUrl);
                        const pdf = await loadingTask.promise;

                        container.innerHTML = '';

                        for (let pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++) {
                            const page = await pdf.getPage(pageNumber);
                            await renderPage(page);
                        }

                        if (statusEl) statusEl.textContent = `${pdf.numPages} halaman berhasil dimuat`;
                    } catch (error) {
                        console.error(error);
                        if (statusEl) statusEl.textContent = 'Gagal memuat PDF.';
                        container.innerHTML =
                            '<div class="alert alert-danger mb-0">Gagal memuat preview PDF.</div>';
                    }
                };

                loadPdf();
            })();
        </script>
    @endif
@endpush
