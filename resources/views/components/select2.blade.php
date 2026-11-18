{{--
    Komponen <select> dengan pencarian (Select2 / SearchableSelect) — generik untuk semua form.

    Props:
        options            Collection / array data. Bisa berupa:
                             - array asosiatif sederhana  : ['1' => 'Admin', '2' => 'OPD']
                             - collection / array objek   : $skpdList, $users, dll. (pakai valueField & labelField)
        valueField         Nama field untuk value option     (default: 'id')
        labelField         Nama field untuk teks option      (default: 'name')
        codeField          (opsional) Field kode, tampil sebagai badge + ikut dicari
        selected           Nilai terpilih (string / array untuk multiple). Default: old(name)
        placeholder        Placeholder dropdown
        searchPlaceholder  Placeholder kolom pencarian
        noResults          Pesan jika tidak ada hasil
        allowClear         Tampilkan tombol hapus pilihan (default: true)

    Contoh:
        # Data dari model
        <x-select2 id="create_dok_skpd" name="dok_skpd" :options="$skpdList"
            value-field="opd_kode" label-field="opd_nama" code-field="opd_kode"
            placeholder="-- Pilih SKPD / OPD --" required />

        # Array sederhana
        <x-select2 id="role" name="role" :options="['0' => 'Admin', '1' => 'OPD']" :allow-clear="false" />

        # Option manual lewat slot
        <x-select2 id="tahun" name="tahun">
            <option value="2025">2025</option>
            <option value="2026">2026</option>
        </x-select2>

        # Multiple
        <x-select2 id="tags" name="tags[]" :options="$tags" multiple :selected="[1, 3]" />

    Aset yang wajib di-include pada halaman:
        @include('_partials.searchable-select.styles')   → di @section('page-style')
        @include('_partials.searchable-select.scripts')  → di @section('page-script'), setelah jQuery
--}}
@props([
    'options' => [],
    'valueField' => 'id',
    'labelField' => 'name',
    'codeField' => null,
    'selected' => null,
    'placeholder' => '-- Pilih --',
    'searchPlaceholder' => 'Ketik untuk mencari...',
    'noResults' => 'Data tidak ditemukan',
    'allowClear' => true,
])

@php
    $isMultiple = $attributes->has('multiple');
    $fieldName = rtrim((string) $attributes->get('name', ''), '[]');
    $selectedValues = collect(
        $selected ?? ($fieldName !== '' ? old($fieldName) : null) ?? []
    )->map(fn($v) => (string) $v)->all();

    // Normalisasi options → [ ['value' => .., 'label' => .., 'code' => ..], ... ]
    $items = [];
    foreach ($options as $key => $item) {
        if (is_scalar($item) || $item === null) {
            $items[] = ['value' => (string) $key, 'label' => (string) $item, 'code' => null];
        } else {
            $items[] = [
                'value' => (string) data_get($item, $valueField),
                'label' => (string) data_get($item, $labelField),
                'code' => $codeField ? (string) data_get($item, $codeField) : null,
            ];
        }
    }
@endphp

<select {{ $attributes->merge(['class' => 'form-select']) }}
    data-searchable-select
    data-placeholder="{{ $placeholder }}"
    data-search-placeholder="{{ $searchPlaceholder }}"
    data-no-results="{{ $noResults }}"
    data-allow-clear="{{ $allowClear ? 'true' : 'false' }}">
    @unless ($isMultiple)
        <option value=""></option>
    @endunless

    @if (trim($slot) !== '')
        {{ $slot }}
    @else
        @foreach ($items as $opt)
            <option value="{{ $opt['value'] }}"
                @if ($opt['code'] !== null && $opt['code'] !== '') data-code="{{ $opt['code'] }}" @endif
                data-label="{{ $opt['label'] }}"
                @selected(in_array($opt['value'], $selectedValues, true))>
                {{ $opt['code'] ? '[' . $opt['code'] . '] ' : '' }}{{ $opt['label'] }}
            </option>
        @endforeach
    @endif
</select>
