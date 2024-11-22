<?php if (!defined('ABSPATH')) exit; ?>

<div class="relative">
    <button type="button" 
            class="absolute top-0 right-0 text-gray-400 hover:text-gray-500" 
            id="close-detail">
        <span class="sr-only">Close</span>
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>

    <div class="mt-4">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Detail Provinsi</h2>
        
        <div class="space-y-4">
            <div>
                <h3 class="text-sm font-medium text-gray-500">Kode Provinsi</h3>
                <p class="mt-1 text-lg text-gray-900"><?php echo esc_html($provinsi->kode_provinsi); ?></p>
            </div>

            <div>
                <h3 class="text-sm font-medium text-gray-500">Nama Provinsi</h3>
                <p class="mt-1 text-lg text-gray-900"><?php echo esc_html($provinsi->nama_provinsi); ?></p>
            </div>

            <div>
                <h3 class="text-sm font-medium text-gray-500">Tanggal Dibuat</h3>
                <p class="mt-1 text-gray-900"><?php echo date_i18n('d F Y H:i', strtotime($provinsi->created_at)); ?></p>
            </div>

            <div>
                <h3 class="text-sm font-medium text-gray-500">Terakhir Diperbarui</h3>
                <p class="mt-1 text-gray-900"><?php echo date_i18n('d F Y H:i', strtotime($provinsi->updated_at)); ?></p>
            </div>
        </div>

        <div class="mt-6 flex gap-4">
            <button type="button"
                    onclick="editProvinsi(<?php echo esc_js($provinsi->id); ?>)"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Edit
            </button>
            <button type="button"
                    onclick="deleteProvinsi(<?php echo esc_js($provinsi->id); ?>)"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                Hapus
            </button>
        </div>
    </div>
</div>
