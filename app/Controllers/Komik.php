<?php

namespace App\Controllers;

use App\Models\KomikModel;

class Komik extends BaseController
{
    protected $komikModel;

    public function __construct()
    {
        $this->komikModel = new KomikModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Daftar Komik',
            'komik' => $this->komikModel->getKomik()
        ];

        return view('komik/index', $data);
    }

    public function detail($slug)
    {
        $data = [
            'title' => 'Detail Komik',
            'komik' => $this->komikModel->getKomik($slug)
        ];

        if (empty($data['komik'])) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Judul komik ' . $slug . ' tidak ditemukan');
        }
        return view('komik/detail', $data);
    }

    public function create()
    {
        //session
        $data = [
            'title' => 'Form Tambah Data Komik',
            'validation' => \Config\Services::validation()
        ];

        return view('komik/create', $data);
    }

    public function save()
    {
        //validasi input
        if (!$this->validate([
            'judul' => [
                'rules' => 'required|is_unique[komik.judul]',
                'errors' => [
                    'required' => '{field} komik harus diisi.',
                    'is_unique' => '{field} komik sudah terdaftar.'
                ]
            ],
            'penulis' => [
                'rules' => 'required',
                'errors' => [
                    'required' => '{field} komik harus diisi.'
                ]
            ],
            'penerbit' => [
                'rules' => 'required',
                'errors' => [
                    'required' => '{field} komik harus diisi.'
                ]
            ],
            'sampul' => [
                'rules' => 'max_size[sampul,1024]|is_image[sampul]|mime_in[sampul,image/jpg,image/jpeg,image/png]',
                'errors' => [
                    'max_size' => 'File yang anda upload terlalu besar (maks 1MB)',
                    'is_image' => 'File yang anda pilih bukan gambar',
                    'mime_in' => 'File yang anda pilih bukan gambar'
                ]
            ]
        ])) {
            return redirect()->to('/komik/create')->withInput();
        }

        //ambil file gambar nya
        $fileGambar = $this->request->getFile('sampul');

        //jika tidak ada gambar yg diupload,
        //maka gunakan file gamabr default.jpg
        if ($fileGambar->getError() == 4) {
            $fileName = 'default.jpg';
        } else {
            $fileName = $fileGambar->getRandomName();
            //pindahkan gambar ke folder /img
            $fileGambar->move('img', $fileName);
        }


        $slugName = url_title($this->request->getVar('judul'), '-', true);
        $this->komikModel->save([
            'judul' => $this->request->getVar('judul'),
            'slug' => $slugName,
            'penulis' => $this->request->getVar('penulis'),
            'penerbit' => $this->request->getVar('penerbit'),
            'sampul' => $fileName
        ]);

        session()->setFlashData('pesan', 'Data berhasil ditambahkan.');

        return redirect()->to('/komik');
    }

    public function delete($id)
    {
        $sampulName = $this->komikModel->find($id)['sampul'];
        $this->komikModel->delete($id);
        if ($sampulName != 'default.jpg') {
            unlink('img/' . $sampulName);
        }
        session()->setFlashData('pesan', 'Data berhasil dihapus.');
        return redirect()->to('/komik');
    }

    public function edit($slug)
    {
        $data = [
            'title' => 'Form Edit Data Komik',
            'validation' => \Config\Services::validation(),
            'komik' => $this->komikModel->getKomik($slug)
        ];

        if (empty($data['komik'])) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Judul komik ' . $slug . ' tidak ditemukan');
        }

        return view('komik/edit', $data);
    }

    public function update($id)
    {
        //validasi input
        if (!$this->validate([
            'judul' => [
                // 'rules' => $title_rules,
                'rules' => 'required|is_unique[komik.judul,id,' . $id . ']',
                'errors' => [
                    'required' => '{field} komik harus diisi.',
                    'is_unique' => '{field} komik sudah terdaftar.'
                ]
            ],
            'penulis' => [
                'rules' => 'required',
                'errors' => [
                    'required' => '{field} komik harus diisi.'
                ]
            ],
            'penerbit' => [
                'rules' => 'required',
                'errors' => [
                    'required' => '{field} komik harus diisi.'
                ]
            ],
            'sampul' => [
                'rules' => 'max_size[sampul,1024]|is_image[sampul]|mime_in[sampul,image/jpg,image/jpeg,image/png]',
                'errors' => [
                    'max_size' => 'File yang anda upload terlalu besar (maks 1MB)',
                    'is_image' => 'File yang anda pilih bukan gambar',
                    'mime_in' => 'File yang anda pilih bukan gambar'
                ]
            ]
        ])) {
            return redirect()->to('/komik/edit/' . $this->request->getVar('slug'))->withInput();
        }

        //ambil file gambar nya
        $fileGambar = $this->request->getFile('sampul');

        //jika tidak ada gambar yg diupload,
        //maka gunakan file gambar yang telah ada 
        if ($fileGambar->getError() == 4) {
            $fileName = $this->request->getVar('sampul');
        } else {
            $fileName = $fileGambar->getRandomName();
            //pindahkan gambar ke folder /img
            $fileGambar->move('img', $fileName);
            if ($this->request->getVar('sampul') != 'default.jpg') {
                unlink('img/' . $this->request->getVar('sampul'));
            }
        }

        $slug = url_title($this->request->getVar('judul'), '-', true);
        $this->komikModel->save([
            'id' => $id,
            'judul' => $this->request->getVar('judul'),
            'slug' => $slug,
            'penulis' => $this->request->getVar('penulis'),
            'penerbit' => $this->request->getVar('penerbit'),
            'sampul' => $fileName
        ]);

        session()->setFlashData('pesan', 'Data berhasil diupdate.');
        return redirect()->to('/komik/' . $slug);
    }
}
