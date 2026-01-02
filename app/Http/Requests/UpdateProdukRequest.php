<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProdukRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $params = $this->route('params');
        return [
            'kode' => 'unique:produks,kode,' . $params . ',uuid',
            'uuid_kategori' => 'required',
            'sub_kategori' => 'required',
            'uuid_suplayer' => 'required',
            'nama_barang' => 'required',
            'merek' => 'required',
            'hrg_modal' => 'required',
            'profit' => 'required',
            'minstock' => 'required',
            'maxstock' => 'required',
            'satuan' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'kode.unique' => 'Kode sudah digunakan.',
            'uuid_kategori.required' => 'Kolom nama kategori harus di isi.',
            'sub_kategori.required' => 'Kolom nama sub kategori harus di isi.',
            'uuid_suplayer.required' => 'Kolom suplayer harus di isi.',
            'nama_barang.required' => 'Kolom nama barang harus di isi.',
            'merek.required' => 'Kolom merek harus di isi.',
            'hrg_modal.required' => 'Kolom hrg modal harus di isi.',
            'profit.required' => 'Kolom profit harus di isi.',
            'minstock.required' => 'Kolom minstock harus di isi.',
            'maxstock.required' => 'Kolom maxstock harus di isi.',
            'satuan.required' => 'Kolom satuan harus di isi.',
        ];
    }
}
