<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Barang extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('m_barang');
        $this->load->model('m_user');
        $this->load->helper(array('form', 'url'));
    }

    public function index()
    {
        $data['title'] = 'List Barang | DHC Herbal';
        $data['user'] = $this->m_user->user($this->session->userdata['email']);
        $data['list'] = $this->m_barang->get_all();
        $data['supplier'] = $this->db->get('supplier')->result_array();

        $this->load->view('user/template/header', $data);
        $this->load->view('user/template/navbar', $data);
        $this->load->view('user/content/barang', $data);
        $this->load->view('user/template/footer', $data);
    }

    public function edit_supplier($id_supplier)
    {
        $data['title'] = 'Edit Info Supplier';
        $data['info'] = $this->m_barang->get_info_supplier($id_supplier);

        $this->load->view('user/template/header', $data);
        $this->load->view('user/template/navbar', $data);
        $this->load->view('user/content/edit_supplier', $data);
        $this->load->view('user/template/footer', $data);
    }

    public function hapus_barang($id)
    {
        $this->db->where('id_barang', $id);
        $this->db->delete('barang');
        $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Berhasil Menghapus Barang!</div>');
        redirect('barang');
    }

    public function hapus_supplier($id)
    {
        $this->db->where('id_supplier', $id);
        $this->db->delete('supplier');
        $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Berhasil Menghapus Supplier!</div>');
        redirect('barang/lihat_supplier');
    }

    public function tambah_barang()
    {
        $this->form_validation->set_rules('nama_barang', 'Nama_barang', 'required|trim');
        $this->form_validation->set_rules('stok', 'Stok', 'required|numeric');
        $this->form_validation->set_rules('satuan', 'Satuan', 'required|is_natural');
        $this->form_validation->set_rules('supplier', 'Supplier', 'required|is_natural');
        $this->form_validation->set_rules('hrg_beli', 'Hrg_beli', 'required|numeric');
        $this->form_validation->set_rules('hrg_jual', 'Hrg_jual', 'required|numeric');
        $this->form_validation->set_rules('keterangan', 'Keterangan', 'required|trim');

        if ($this->form_validation->run() == FALSE) {
            $data['title'] = 'Tambah Barang | DHC Herbal';
            $data['user'] = $this->m_user->user($this->session->userdata['email']);
            $data['supplier'] = $this->db->get('supplier')->result_array();
            $data['satuan'] = $this->db->get('satuan')->result_array();

            $this->load->view('user/template/header', $data);
            $this->load->view('user/template/navbar', $data);
            $this->load->view('user/content/tambah_barang', $data);
            $this->load->view('user/template/footer', $data);
        } else {
            $insert = [
                'nama_barang' => $this->input->post('nama_barang'),
                'stock' => htmlspecialchars($this->input->post('stok')),
                'satuan' => $this->input->post('satuan'),
                'supplier' => $this->input->post('supplier'),
                'hrg_beli' => htmlspecialchars($this->input->post('hrg_beli')),
                'hrg_jual' => htmlspecialchars($this->input->post('hrg_jual')),
                'khasiat' => htmlspecialchars($this->input->post('keterangan'))
            ];
            $this->db->insert('barang', $insert);
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Berhasil Menambahkan Barang!</div>');
            redirect('barang/tambah_barang');
        }
    }

    public function lihat_lengkap($id)
    {
        $data['title'] = 'Informasi Barang';
        $data['user'] = $this->m_user->user($this->session->userdata['email']);

        $this->db->join('supplier',  'supplier.id_supplier = barang.supplier');
        $data['barang'] = $this->db->get_where('barang', ['id_barang' => $id])->row_array();

        $this->db->group_by(array('timestamp', 'title'));
        $data['wholesales'] = $this->db->get_where('pos', ['item' => $data['barang']['nama_barang']]);

        $this->load->view('user/template/header', $data);
        $this->load->view('user/template/navbar', $data);
        $this->load->view('user/content/lihat_barang', $data);
        $this->load->view('user/template/footer', $data);
    }

    public function lihat_supplier()
    {
        $data['title'] = 'Supplier DHC Herbal';
        $data['user'] = $this->m_user->user($this->session->userdata['email']);
        $data['supplier'] = $this->db->get('supplier')->result_array();

        $this->load->view('user/template/header', $data);
        $this->load->view('user/template/navbar', $data);
        $this->load->view('user/content/lihat_supplier', $data);
        $this->load->view('user/template/footer', $data);
    }

    public function tambah_satuan()
    {
        $this->form_validation->set_rules('nama', 'Nama', 'required|trim');

        if ($this->form_validation->run() == TRUE) {
            $insert = [
                'satuan' => htmlspecialchars($this->input->post('nama'))
            ];
            $this->db->insert('satuan', $insert);
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Berhasil Menambahkan Satuan!</div>');
            redirect('barang/tambah_barang');
        } else {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Gagal Menambahkan Satuan!</div>');
            redirect('barang/tambah_barang');
        }
    }

    public function tambah_supplier()
    {
        $this->form_validation->set_rules('nama_supplier', 'Nama_supplier', 'required|trim');
        $this->form_validation->set_rules('cp', 'Cp', 'required|trim');
        $this->form_validation->set_rules('no', 'No', 'required');

        if ($this->form_validation->run() == TRUE) {
            $insert = [
                'supplier' => $this->input->post('nama_supplier'),
                'cp' => $this->input->post('cp'),
                'nohp' => $this->input->post('no')
            ];
            $this->db->insert('supplier', $insert);
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Berhasil Menambahkan Supplier!</div>');
            redirect('barang/tambah_barang');
        } else {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Gagal Menambahkan Supplier!</div>');
            redirect('barang/tambah_barang');
        }
    }
}
