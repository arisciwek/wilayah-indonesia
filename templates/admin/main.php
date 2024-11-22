<?php if (!defined('ABSPATH')) exit; ?>
<div class="wrap">
    <div class="flex flex-col md:flex-row md:gap-6 p-4">
        <!-- Panel Kiri: Daftar Provinsi -->
        <div class="w-full md:w-2/3 transition-all duration-300" id="provinsi-list-panel">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold text-gray-800">Daftar Provinsi</h1>
                    <div class="flex gap-2">
                        <button type="button" 
                                id="btn-import-provinsi"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                            Import Excel
                        </button>
                        <button type="button" 
                                id="btn-add-provinsi"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Tambah Provinsi
                        </button>
                    </div>
                </div>

                <!-- DataTable Provinsi -->
                <table id="provinsi-table" class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Kode
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Nama Provinsi
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <!-- Data will be populated by DataTables -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Panel Kanan: Detail Provinsi -->
        <div class="w-full md:w-1/3 mt-6 md:mt-0 hidden transition-all duration-300 bg-white shadow-lg" id="detail-panel">
            <div class="p-6 sticky top-8">
                <div id="detail-content">
                    <!-- Content will be loaded via AJAX -->
                </div>
            </div>
        </div>

    <!-- Modal Import Excel -->
    <div id="import-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h2 class="text-xl font-medium text-gray-900">Import Data Provinsi</h2>
                <form id="import-form" class="mt-4">
                    <?php wp_nonce_field('wilayah_indonesia_nonce'); ?>

                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700">File Excel/CSV</label>
                        <input type="file" 
                               name="import_file" 
                               id="import_file" 
                               accept=".xlsx,.xls,.csv"
                               class="mt-1 block w-full text-sm text-gray-500
                                      file:mr-4 file:py-2 file:px-4
                                      file:rounded-full file:border-0
                                      file:text-sm file:font-semibold
                                      file:bg-blue-50 file:text-blue-700
                                      hover:file:bg-blue-100"
                               required>
                        <p class="mt-1 text-sm text-gray-500">Format file: XLSX, XLS, atau CSV</p>
                    </div>

                    <div class="mt-4">
                        <a href="#" id="download-template" class="text-sm text-blue-600 hover:text-blue-800">
                            Download Template Excel
                        </a>
                    </div>

                    <div class="flex justify-end gap-4 mt-6">
                        <button type="button" 
                                id="btn-cancel-import"
                                class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Form Provinsi -->
    <div id="provinsi-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h2 class="text-xl font-medium text-gray-900" id="modal-title">Tambah Provinsi</h2>
                <form id="provinsi-form" class="mt-4">
                    <input type="hidden" id="provinsi-id" name="id" value="">
                    <?php wp_nonce_field('wilayah_indonesia_nonce'); ?>

                    <div class="mt-4">
                        <label for="kode_provinsi" class="block text-sm font-medium text-gray-700">Kode Provinsi</label>
                        <input type="text" 
                               name="kode_provinsi" 
                               id="kode_provinsi" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               required>
                    </div>

                    <div class="mt-4">
                        <label for="nama_provinsi" class="block text-sm font-medium text-gray-700">Nama Provinsi</label>
                        <input type="text" 
                               name="nama_provinsi" 
                               id="nama_provinsi" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               required>
                    </div>

                    <div class="flex justify-end gap-4 mt-6">
                        <button type="button" 
                                id="btn-cancel"
                                class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
