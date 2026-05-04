<script setup lang="ts">
import { ref, onUnmounted } from 'vue'
import { BrowserMultiFormatReader } from '@zxing/browser'

const emit = defineEmits<{
    scanned: [code: string]
    close: []
}>()

const videoRef  = ref<HTMLVideoElement | null>(null)
const error     = ref<string | null>(null)
const scanning  = ref(false)

let reader: BrowserMultiFormatReader | null = null
let streamRef: MediaStream | null = null

async function start() {
    error.value   = null
    scanning.value = true

    try {
        reader = new BrowserMultiFormatReader()
        const devices = await BrowserMultiFormatReader.listVideoInputDevices()

        if (!devices.length) {
            error.value = 'Nenhuma câmera encontrada.'
            scanning.value = false
            return
        }

        // Prefer back camera
        const device = devices.find(d => /back|rear|environment/i.test(d.label)) ?? devices[0]

        await reader.decodeFromVideoDevice(device.deviceId, videoRef.value!, (result) => {
            if (result) {
                emit('scanned', result.getText())
                stop()
            }
        })
    } catch (e: any) {
        error.value = e?.message ?? 'Erro ao acessar câmera.'
        scanning.value = false
    }
}

function stop() {
    try { reader?.reset() } catch {}
    scanning.value = false
}

function close() {
    stop()
    emit('close')
}

// Start scanning when component mounts
start()

onUnmounted(stop)
</script>

<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4">
        <div class="bg-white rounded-2xl overflow-hidden w-full max-w-sm shadow-2xl">
            <!-- Header -->
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h2 class="font-medium text-gray-900">Escanear código</h2>
                <button @click="close"
                    class="text-gray-400 hover:text-gray-700 text-xl leading-none">&times;</button>
            </div>

            <!-- Camera view -->
            <div class="relative bg-black aspect-square overflow-hidden">
                <video ref="videoRef" class="w-full h-full object-cover" muted playsinline />

                <!-- Scan frame overlay -->
                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <div class="w-56 h-56 border-2 border-white/70 rounded-xl relative">
                        <!-- Corner accents -->
                        <span class="absolute top-0 left-0 w-6 h-6 border-t-4 border-l-4 border-white rounded-tl-lg" />
                        <span class="absolute top-0 right-0 w-6 h-6 border-t-4 border-r-4 border-white rounded-tr-lg" />
                        <span class="absolute bottom-0 left-0 w-6 h-6 border-b-4 border-l-4 border-white rounded-bl-lg" />
                        <span class="absolute bottom-0 right-0 w-6 h-6 border-b-4 border-r-4 border-white rounded-br-lg" />
                        <!-- Scan line animation -->
                        <div class="absolute left-1 right-1 h-0.5 bg-red-400/80 top-1/2 animate-pulse" />
                    </div>
                </div>

                <p v-if="scanning && !error"
                    class="absolute bottom-3 left-0 right-0 text-center text-xs text-white/80">
                    Aponte para o código de barras ou QR Code
                </p>
            </div>

            <!-- Error -->
            <div v-if="error" class="px-5 py-4 text-sm text-red-600 bg-red-50">
                {{ error }}
            </div>

            <!-- Manual input fallback -->
            <div class="px-5 py-4 border-t border-gray-100">
                <p class="text-xs text-gray-400 mb-2">Ou digite manualmente:</p>
                <div class="flex gap-2">
                    <input
                        type="text"
                        placeholder="Código de barras..."
                        class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400"
                        @keydown.enter.prevent="(e) => { emit('scanned', (e.target as HTMLInputElement).value); close() }"
                    />
                    <button
                        @click="(e) => {
                            const input = (e.currentTarget as HTMLElement).previousElementSibling as HTMLInputElement
                            if (input.value) { emit('scanned', input.value); close() }
                        }"
                        class="px-3 py-2 text-sm bg-gray-900 text-white rounded-lg hover:bg-gray-700">
                        OK
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
