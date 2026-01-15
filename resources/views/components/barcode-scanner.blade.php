@props([
    'inputId' => 'codigo_barras'
])

<!-- Modal del Escáner -->
<div id="barcodeScannerModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-2xl max-w-2xl w-full">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 rounded-t-lg">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-camera mr-2"></i>
                        Escanear Código de Barras / QR
                    </h3>
                    <p class="text-blue-100 text-sm mt-1">Posiciona el código de barras o QR frente a la cámara</p>
                </div>
                <button onclick="closeBarcodeScanner()" class="text-white hover:text-gray-200 transition-colors">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
        </div>

        <!-- Body -->
        <div class="p-6">
            <!-- Estado de la cámara -->
            <div id="scannerStatus" class="mb-4 p-3 rounded-lg bg-blue-50 border border-blue-200">
                <p class="text-sm text-blue-800 flex items-center">
                    <i class="fas fa-info-circle mr-2"></i>
                    <span id="statusText">Iniciando cámara...</span>
                </p>
            </div>

            <!-- Área del escáner -->
            <div class="relative">
                <div id="barcode-reader" class="rounded-lg overflow-hidden bg-gray-900" style="min-height: 400px;">
                    <!-- El escáner se renderizará aquí -->
                </div>

                <!-- Overlay de ayuda -->
                <div class="absolute inset-0 pointer-events-none flex items-center justify-center">
                    <div class="border-2 border-white border-dashed rounded-lg w-3/4 h-1/2 opacity-50"></div>
                </div>
            </div>

            <!-- Resultado del escaneo -->
            <div id="scanResult" class="mt-4 p-4 rounded-lg bg-green-50 border border-green-200 hidden">
                <p class="text-sm font-medium text-green-800 flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    Código detectado: <span id="scannedCode" class="ml-2 font-mono font-bold"></span>
                </p>
            </div>

            <!-- Botones -->
            <div class="mt-6 flex justify-between items-center">
                <div class="text-sm text-gray-600">
                    <i class="fas fa-lightbulb mr-1 text-yellow-500"></i>
                    <strong>Consejo:</strong> Mantén el código estable y bien iluminado. Funciona con códigos de barras y códigos QR.
                </div>
                <div class="flex gap-3">
                    <x-button 
                        type="button" 
                        variant="secondary" 
                        icon="fas fa-times"
                        onclick="closeBarcodeScanner()"
                    >
                        Cancelar
                    </x-button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    #barcode-reader {
        position: relative;
    }
    
    #barcode-reader video {
        width: 100%;
        height: auto;
        border-radius: 0.5rem;
    }

    #barcode-reader__scan_region {
        border: 2px solid rgba(59, 130, 246, 0.5) !important;
    }

    #barcode-reader__dashboard_section_csr {
        display: none !important;
    }
</style>
@endpush

@push('scripts')
<script>
    let html5QrCode = null;
    let isScanning = false;
    const targetInputId = '{{ $inputId }}';

    // Hacer funciones globales
    window.openBarcodeScanner = async function() {
        const modal = document.getElementById('barcodeScannerModal');
        modal.classList.remove('hidden');
        
        await startScanner();
    };

    window.closeBarcodeScanner = async function() {
        await stopScanner();
        
        const modal = document.getElementById('barcodeScannerModal');
        modal.classList.add('hidden');
        
        // Limpiar resultado
        const resultDiv = document.getElementById('scanResult');
        resultDiv.classList.add('hidden');
    };

    async function startScanner() {
        if (isScanning) return;

        try {
            updateStatus('Iniciando cámara...', 'info');

            // Usar la clase Html5Qrcode del window
            const Html5QrcodeScanner = window.Html5Qrcode;
            if (!Html5QrcodeScanner) {
                throw new Error('La librería de escaneo no está cargada');
            }

            html5QrCode = new Html5QrcodeScanner("barcode-reader");

            const config = {
                fps: 10,
                qrbox: { width: 300, height: 200 },
                aspectRatio: 1.777778,
                // Soportar tanto códigos de barras como QR
                formatsToSupport: [
                    0, // CODE_128
                    1, // CODE_39
                    3, // EAN_13
                    4, // EAN_8
                    5, // UPC_A
                    6, // UPC_E
                    7, // QR_CODE
                    8, // CODE_93
                    9, // CODE_11
                ]
            };

            await html5QrCode.start(
                { facingMode: "environment" },
                config,
                onScanSuccess,
                onScanError
            );

            isScanning = true;
            updateStatus('Cámara activa - Posiciona el código de barras', 'success');

        } catch (err) {
            console.error('Error al iniciar escáner:', err);
            updateStatus(`Error: ${err.message || 'No se pudo acceder a la cámara'}`, 'error');
            
            // Intentar con cámara frontal si falla la trasera
            if (err.message && err.message.includes('facingMode')) {
                try {
                    await html5QrCode.start(
                        { facingMode: "user" },
                        config,
                        onScanSuccess,
                        onScanError
                    );
                    isScanning = true;
                    updateStatus('Cámara frontal activa - Posiciona el código de barras', 'success');
                } catch (err2) {
                    console.error('Error con cámara frontal:', err2);
                }
            }
        }
    }

    async function stopScanner() {
        if (html5QrCode && isScanning) {
            try {
                await html5QrCode.stop();
                await html5QrCode.clear();
            } catch (err) {
                console.error('Error al detener escáner:', err);
            }
            isScanning = false;
        }
    }

    function onScanSuccess(decodedText, decodedResult) {
        console.log('Código escaneado:', decodedText);
        
        // Mostrar resultado
        document.getElementById('scannedCode').textContent = decodedText;
        document.getElementById('scanResult').classList.remove('hidden');
        
        // Actualizar el input del formulario
        const targetInput = document.getElementById(targetInputId);
        if (targetInput) {
            targetInput.value = decodedText;
            
            // Animación visual en el input
            targetInput.classList.add('border-green-500', 'bg-green-50');
            setTimeout(() => {
                targetInput.classList.remove('border-green-500', 'bg-green-50');
            }, 2000);
        }
        
        // Reproducir sonido de éxito (opcional)
        playSuccessSound();
        
        // Cerrar modal después de 1.5 segundos
        setTimeout(() => {
            window.closeBarcodeScanner();
        }, 1500);
    }

    function onScanError(errorMessage) {
        // Ignorar errores de escaneo normales (cuando no detecta código)
        // Solo mostrar errores críticos
    }

    function updateStatus(message, type = 'info') {
        const statusDiv = document.getElementById('scannerStatus');
        const statusText = document.getElementById('statusText');
        
        statusText.textContent = message;
        
        // Cambiar colores según el tipo
        statusDiv.className = 'mb-4 p-3 rounded-lg border';
        
        if (type === 'success') {
            statusDiv.classList.add('bg-green-50', 'border-green-200');
            statusText.className = 'text-sm text-green-800 flex items-center';
            statusText.innerHTML = `<i class="fas fa-check-circle mr-2"></i>${message}`;
        } else if (type === 'error') {
            statusDiv.classList.add('bg-red-50', 'border-red-200');
            statusText.className = 'text-sm text-red-800 flex items-center';
            statusText.innerHTML = `<i class="fas fa-exclamation-circle mr-2"></i>${message}`;
        } else {
            statusDiv.classList.add('bg-blue-50', 'border-blue-200');
            statusText.className = 'text-sm text-blue-800 flex items-center';
            statusText.innerHTML = `<i class="fas fa-info-circle mr-2"></i>${message}`;
        }
    }

    function playSuccessSound() {
        // Crear un beep de éxito
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();
        
        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);
        
        oscillator.frequency.value = 800;
        oscillator.type = 'sine';
        
        gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.2);
        
        oscillator.start(audioContext.currentTime);
        oscillator.stop(audioContext.currentTime + 0.2);
    }

    // Limpiar al salir de la página
    window.addEventListener('beforeunload', async () => {
        await stopScanner();
    });
</script>
@endpush
