<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;
use RealRashid\SweetAlert\Facades\Alert;
use App\Models\JenisProduk;
use DB;

class ProdukController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // index untuk produk
        $produk = Produk::join('jenis_produk', 'jenis_produk_id', '=', 'jenis_produk.id')
        ->select('produk.*', 'jenis_produk.nama as jenis')
        ->get();
        
        return view ('admin.produk.index', compact('produk'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $jenis_produk = DB::table('jenis_produk')->get();
        return view('admin.produk.create', compact('jenis_produk'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
           'kode' => 'required|unique:produk|max:10',
           'nama' => 'required|max:45',
           'harga_beli' => 'required|numeric',
           'harga_jual' => 'required|numeric',
           'stok' => 'required|numeric',
           'min_stok' => 'required|numeric',
           'foto' => 'nullable|image|mimes:jpg,png,jpeg,gif,svg|max:2048',
           

        ],
        [
            'kode.max' => 'Kode Maksimal 10 Karakter',
            'kode.required' => 'Kolom Kode Wajib diisi',
            'kode.unique' => 'Kode Tidak Boleh Sama',
            'nama.required' => 'Kolom Nama Wajib diisi',
            'nama.max' => 'Nama Maksimal 45 Karakter',
            'harga_beli.required' => 'Kolom Ini Wajib Diisi',
            'harga_jual.required' => 'Kolom Ini Wajib Diisi',
            'stok.required' => 'Kolom Ini Wajib Diisi',
            'min_stok.required' => 'Kolom Ini Wajib Diisi',
            'foto.max' => 'Foto Maksimal 2MB',
            'foto.mimes' => 'File Ekstensi hanya bisa jpg, png, jpeg, gif, svg',
        ]
    
    );


        //proses upload foto
        //jika file foto ada yang terupload
        if(!empty($request->foto)){
            //maka proses berikut yang dijalankan
            $fileName = 'foto-'.uniqid().'.'.$request->foto->extension();
            //setelah tau fotonya sudah masuk maka tempatkan ke public
            $request->foto->move(public_path('admin/image'), $fileName);
        } else {
            $fileName = '';
        }
        //tambah data produk
        DB::table('produk')->insert([
            'kode'=>$request->kode,
            'nama'=>$request->nama,
            'harga_jual'=>$request->harga_jual,
            'harga_beli'=>$request->harga_beli,
            'stok'=>$request->stok,
            'min_stok'=>$request->min_stok,
            'deskripsi' => $request->deskripsi,
            'foto'=>$fileName,
            'jenis_produk_id'=>$request->jenis_produk_id,
        ]);
        Alert::success('Tambahkan Produk', 'Berhasil Menambahkan Produk');
        return redirect('admin/produk');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $produk = Produk::join('jenis_produk', 'jenis_produk_id', '=', 'jenis_produk.id')
        ->select('produk.*', 'jenis_produk.nama as jenis')
        ->where('produk.id', $id)
        ->get();
        return view('admin.produk.detail', compact('produk'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //jenis_produk
        $jenis_produk = DB::table('jenis_produk')->get();
        $produk = DB::table('produk')->where('id', $id)->get();
        return view('admin.produk.edit', compact('jenis_produk', 'produk'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        //foto lama 
        $fotoLama = DB::table('produk')->select('foto')->where('id',$id)->get();
        foreach($fotoLama as $f1){
            $fotoLama = $f1->foto;
        }
        //jika foto sudah ada yang terupload 
        if(!empty($request->foto)){
            //maka proses selanjutnya 
        if(!empty($fotoLama->foto)) unlink(public_path('admin/image'.$fotoLama->foto));
        //proses ganti foto
            $fileName = 'foto-'.$request->id.'.'.$request->foto->extension();
            //setelah tau fotonya sudah masuk maka tempatkan ke public
            $request->foto->move(public_path('admin/image'), $fileName);
        } else{
            $fileName = $fotoLama;
        }
        DB::table('produk')->where('id', $id)->update([
            'kode'=>$request->kode,
            'nama'=>$request->nama,
            'harga_jual'=>$request->harga_jual,
            'harga_beli'=>$request->harga_beli,
            'stok'=>$request->stok,
            'min_stok'=>$request->min_stok,
            'deskripsi' => $request->deskripsi,
            'foto'=>$fileName,
            'jenis_produk_id'=>$request->jenis_produk_id,
        ]);
        // Alert::success('Update Produk', 'Berhasil Update Produk');
        return redirect('admin/produk')->with('success', 'Berhasil Menambahka  Produk');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //Fungsi hpus dengan query builder
        DB::table('produk')->where('id', $id)->delete();
        return redirect ('admin/produk');
    }
}
