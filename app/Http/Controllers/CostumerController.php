<?php

namespace App\Http\Controllers;

use App\Models\Costumer;
use App\Models\Jasa;
use Illuminate\Http\Request;

class CostumerController extends BaseController
{
    public function index()
    {
        $module = 'Costumer';
        return view('pages.costumer.index', compact('module'));
    }

    public function get(Request $request)
    {
        $columns = [
            'uuid',
            'nama',
            'alamat',
            'nomor',
            'plat',
            'uuid_penjualan',
        ];

        $totalData = Costumer::count();

        // HILANGKAN penjualan.jasa karena array tidak bisa eager load
        $query = Costumer::with(['penjualan'])->select($columns);

        // Searching
        if (!empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search, $columns) {
                foreach ($columns as $column) {
                    $q->orWhere($column, 'like', "%{$search}%");
                }
            });
        }

        $totalFiltered = $query->count();

        // Sorting
        if ($request->order) {
            $orderCol = $columns[$request->order[0]['column']];
            $orderDir = $request->order[0]['dir'];
            $query->orderBy($orderCol, $orderDir);
        } else {
            $query->latest();
        }

        // Pagination
        $query->skip($request->start)->take($request->length);

        // Format Data
        $data = $query->get()->map(function ($item) {

            $jasaList = [];

            if (
                $item->penjualan
                && is_array($item->penjualan->uuid_jasa)
                && count($item->penjualan->uuid_jasa) > 0
            ) {
                $jasaData = Jasa::whereIn('uuid', $item->penjualan->uuid_jasa)->get();

                foreach ($jasaData as $jasa) {
                    $jasaList[] = $jasa->nama;
                }
            }

            return [
                'uuid'   => $item->uuid,
                'nama'   => $item->nama,
                'alamat' => $item->alamat,
                'nomor'  => $item->nomor,
                'plat'   => $item->plat,
                'bukti'  => $item->penjualan->no_bukti ?? '-',
                'jasa'   => $jasaList,
            ];
        });

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data
        ]);
    }

    public function delete($params)
    {
        Costumer::where('uuid', $params)->delete();
        return response()->json(['status' => 'success']);
    }

    public function getCostumerByPlat(Request $request)
    {
        $plat = $request->input('plat');
        $costumer = Costumer::where('plat', $plat)->first();

        if ($costumer) {
            return response()->json([
                'status' => 'success',
                'data' => $costumer
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Costumer not found'
        ], 404);
    }

    public function getCostumerByPlatBoot($params)
    {
        $customer = Costumer::with('penjualan')
            ->where('plat', $params)
            ->first();

        // Jika customer tidak ditemukan
        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer tidak ditemukan'
            ], 404);
        }

        $jasaList = [];

        if (
            $customer->penjualan &&
            !empty($customer->penjualan->uuid_jasa)
        ) {
            // Pastikan uuid_jasa berupa array
            $uuidJasa = is_array($customer->penjualan->uuid_jasa)
                ? $customer->penjualan->uuid_jasa
                : json_decode($customer->penjualan->uuid_jasa, true);

            if (is_array($uuidJasa) && count($uuidJasa) > 0) {
                $jasaList = Jasa::whereIn('uuid', $uuidJasa)
                    ->pluck('nama')
                    ->toArray();
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'uuid'   => $customer->uuid,
                'nama'   => $customer->nama,
                'alamat' => $customer->alamat,
                'nomor'  => $customer->nomor,
                'plat'   => $customer->plat,
                'bukti'  => $customer->penjualan->no_bukti ?? '-',
                'jasa'   => $jasaList,
            ]
        ]);
    }
}
