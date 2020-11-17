<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class M_barang extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }

    function add_barang($nama, $hrgjual, $hrgbeli, $stock, $satuan)
    {
        $insert = [
            'nama_barang' => $nama,
            'stock' => $stock,
            'satuan' => $satuan,
            'hrg_beli' => $hrgbeli,
            'hrg_jual' => $hrgjual
        ];
        $this->db->insert('barang', $insert);
        $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Berhasil Manambahkan Barang! Silahkan Masuk</div>');
        redirect('barang');
    }

    function get_all()
    {
        $this->db->join('supplier', 'supplier.id_supplier = barang.supplier');
        $this->db->join('satuan', 'satuan.id_satuan = barang.satuan');
        return $this->db->get('barang')->result_array();
    }

    function get_info_supplier($id)
    {
        $this->db->where('id_supplier', $id);
        return $this->db->get('supplier');
    }
}
