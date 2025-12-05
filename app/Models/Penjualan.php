<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Penjualan extends Model
{
    use HasFactory;

    protected $table = 'penjualans';
    protected $primaryKey = 'id';
    protected $fillable = [
        'uuid',
        'uuid_user',
        'uuid_jasa',
        'no_bukti',
        'tanggal_transaksi',
        'pembayaran',
        'discount',
    ];

    protected $casts = [
        'uuid_jasa' => 'array', // otomatis cast ke array pas ambil/simpan
    ];

    protected static function boot()
    {
        parent::boot();

        // Event listener untuk membuat UUID sebelum menyimpan
        static::creating(function ($model) {
            $model->uuid = Uuid::uuid4()->toString();
        });
    }

    // ✅ Relasi yang benar: Penjualan punya banyak detail
    public function detailPenjualans()
    {
        return $this->hasMany(DetailPenjualan::class, 'uuid_penjualans', 'uuid');
    }

    public function jasa()
    {
        return $this->belongsTo(Jasa::class, 'uuid_jasa', 'uuid');
    }

    public function jasaList()
    {
        // kembalikan collection jasa sesuai urutan & duplikat
        $list = collect();

        foreach ($this->uuid_jasa ?? [] as $uuid) {
            $jasa = Jasa::where('uuid', $uuid)->first();
            if ($jasa) {
                $list->push($jasa);
            }
        }

        return $list;
    }
}
