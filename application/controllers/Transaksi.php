<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Transaksi extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('m_barang');
        $this->load->model('m_user');
        $this->load->helper(array('form', 'url'));
    }

    // public function get()
    // {
    //     $data = $this->db->get_where('deliveries', array('student_id' => $row['student_id']))->result_array();
    //     $fetch['fetch'] = $this->db->get_where('homework', array($data[0]['homework_code'] => 'class_id'))->result_array();

    //     $this->load->view('yourview', $fetch);
    // }

    public function index()
    {
        if ($this->session->userdata['email'] == null) {
            redirect('auth');
        }

        $this->form_validation->set_rules('jumlah', 'Jumlah', 'required');

        if ($this->form_validation->run() == true) {
            $insert = [
                'jenis' => 1,
                'keterangan' => 'Omzet Awal',
                'total' => $this->input->post('jumlah'),
                'omzet' => $this->input->post('jumlah')
            ];
            $this->db->insert('riwayat', $insert);
            redirect('transaksi');
        } else {
            $data['title'] = 'Transaksi Baru | DHC System';
            $data['user'] = $this->m_user->user($this->session->userdata['email']);
            $data['list'] = $this->db->get('riwayat')->result_array();

            $this->db->select_sum('total');
            $this->db->where('jenis', 1);
            $data['total_pemasukan'] = $this->db->get('riwayat')->result_array();

            $this->db->select_sum('total');
            $this->db->where('jenis', 2);
            $data['total_pengeluaran'] = $this->db->get('riwayat')->result_array();

            $data['omzet'] = $data['total_pemasukan'][0]['total'] - $data['total_pengeluaran'][0]['total'];

            $this->load->view('user/template/header', $data);
            $this->load->view('user/template/navbar', $data);
            $this->load->view('user/content/riwayat_transaksi', $data);
            $this->load->view('user/template/footer', $data);
        }
    }
    public function tambah_transaksi()
    {
        if ($this->session->userdata['email'] == null) {
            redirect('auth');
        }
        $data['title'] = 'Transaksi Baru | DHC System';
        $data['user'] = $this->m_user->user($this->session->userdata['email']);
        $data['list'] = $this->db->get('barang')->result_array();

        $this->db->join('barang', 'barang.id_barang = temp_transaksi.id_barang');
        $data['list_keranjang'] = $this->db->get('temp_transaksi')->result_array();

        $this->db->select_sum('total');
        $data['total_keranjang'] = $this->db->get('temp_transaksi')->result_array();

        $this->db->order_by('nama_costumer', 'ASC');
        $data['pelanggan'] = $this->db->get('costumer')->result_array();

        $this->load->view('user/template/header', $data);
        $this->load->view('user/template/navbar', $data);
        $this->load->view('user/content/add_transaksi', $data);
        $this->load->view('user/template/footer', $data);
    }

    public function add_without()
    {
        $this->db->join('barang', 'barang.id_barang = temp_transaksi.id_barang');
        $list_keranjang = $this->db->get('temp_transaksi')->result_array();

        if ($list_keranjang == null) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Keranjang Kosong!</div>');
            redirect('transaksi/tambah_transaksi');
        }

        $this->db->select_sum('total');
        $query = $this->db->get('temp_transaksi')->result_array();

        $this->db->order_by('id_riwayat', 'DESC');
        $this->db->limit(1);
        $omzet = $this->db->get('riwayat')->row_array();

        foreach ($list_keranjang as $key) {
            $insert = [
                'barang' => $key['nama_barang'],
                'jumlah' => $key['jumlah'],
                'timestamp' => date('Y-m-d H:i:s', time()),
                'total' => $query[0]['total']
            ];
            $this->db->insert('transaksi', $insert);

            $insertriwayat = [
                'jenis' => 1,
                'keterangan' => $key['nama_barang'],
                'jumlah' => $key['jumlah'],
                'total' => $key['harga'],
                'omzet' => intval($omzet['omzet']) + $key['harga']
            ];
            $this->db->insert('riwayat', $insertriwayat);

            $this->db->where('id_temp', $key['id_temp']);
            $this->db->delete('temp_transaksi');
        }
        $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Keranjang di Checkout!</div>');
        redirect('transaksi/tambah_transaksi');
    }

    public function add_with()
    {
        $this->form_validation->set_rules('id', 'Id', 'required|trim');

        $this->db->where('id_costumer', $this->input->post('id'));
        $get = $this->db->get('costumer')->row_array();


        if ($this->form_validation->run() == false) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Masukkan Pelanggan!</div>');
            redirect('transaksi/tambah_transaksi');
        } else {
            $this->db->join('barang', 'barang.id_barang = temp_transaksi.id_barang');
            $list_keranjang = $this->db->get('temp_transaksi')->result_array();

            if ($list_keranjang == null) {
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Keranjang Kosong!</div>');
                redirect('transaksi/tambah_transaksi');
            }

            $this->db->select_sum('total');
            $query = $this->db->get('temp_transaksi')->result_array();

            $this->db->order_by('id_riwayat');
            $this->db->limit(1);
            $omzet = $this->db->get('riwayat')->row_array();

            foreach ($list_keranjang as $key) {
                $element[] = $key['nama_barang'];
                $insert = [
                    'barang' => $key['nama_barang'],
                    'jumlah' => $key['jumlah'],
                    'timestamp' => date('Y-m-d H:i:s', time()),
                    'total' => $key['harga']
                ];
                $this->db->insert('transaksi', $insert);

                $insertriwayat = [
                    'jenis' => 1,
                    'keterangan' => $key['nama_barang'],
                    'jumlah' => $key['jumlah'],
                    'total' => $key['harga'],
                    'omzet' => intval($omzet['omzet']) + $key['harga']
                ];
                $this->db->insert('riwayat', $insertriwayat);

                $this->db->where('id_temp', $key['id_temp']);
                $this->db->delete('temp_transaksi');
            }

            $string = implode(', ', $element);

            $update = [
                'jml_kunjungan' => intval($get['jml_kunjungan']) + 1,
                'last_purchase' => $string
            ];
            $this->db->where('id_costumer', $get['id_costumer']);
            $this->db->update('costumer', $update);

            $insert2pos = [
                'timestamp' => date('Y-m-d H:i:s', time()),
                'costumer' => $get['nama_costumer'],
                'item' => $string,
                'harga' => $query[0]['total']
            ];
            $this->db->insert('pos', $insert2pos);
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Keranjang di Checkout!</div>');
            redirect('transaksi/tambah_transaksi');
        }
    }

    public function tambah_keranjang()
    {
        if ($this->session->userdata['email'] == null) {
            redirect('auth');
        }
        $this->form_validation->set_rules('id_barang', 'Id_barang', 'required');
        $this->form_validation->set_rules('jumlah', 'Jumlah', 'required');
        $this->form_validation->set_rules('harga', 'Harga', 'required');

        if ($this->form_validation->run() == false) {
            redirect('transaksi/tambah_transaksi');
        } else {
            $this->db->where('id_barang', $this->input->post('id_barang'));
            $rising = $this->db->get('barang')->row_array();

            $risingjml = $rising['stock'] - $this->input->post('jumlah');

            $this->db->set('stock', $risingjml);
            $this->db->where('id_barang', $this->input->post('id_barang'));
            $this->db->update('barang');

            $insert = [
                'id_barang' => $this->input->post('id_barang'),
                'jumlah' => $this->input->post('jumlah'),
                'harga' => $this->input->post('harga'),
                'total' => $this->input->post('harga') * $this->input->post('jumlah'),
                'timestamp' => date('Y-m-d H:i:s', time())
            ];

            $this->db->insert('temp_transaksi', $insert);
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Berhasil Manambahkan Ke Keranjang</div>');
            redirect('transaksi/tambah_transaksi');
        }
    }

    public function hapus_keranjang($id)
    {
        if ($this->session->userdata['email'] == null) {
            redirect('auth');
        }


        $this->db->where('id_temp', $id);
        $rising = $this->db->get('temp_transaksi')->row_array();

        $this->db->where('id_barang', $rising['id_barang']);
        $risingjml = $this->db->get('barang')->row_array();

        $high = $risingjml['stock'] + $rising['jumlah'];

        $this->db->set('stock', $high);
        $this->db->where('id_barang', $rising['id_barang']);
        $this->db->update('barang');

        $this->db->where('id_temp', $id);
        $this->db->delete('temp_transaksi');

        $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Berhasil Menghapus dari Keranjang!</div>');

        redirect('transaksi/tambah_transaksi');
    }
}
