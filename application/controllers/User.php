<?php
defined('BASEPATH') or exit('No direct script access allowed');

class User extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('m_user');
        $this->load->helper(array('form', 'url'));
        date_default_timezone_set("Asia/Bangkok");
    }
    public function index()
    {
        if ($this->session->userdata['email'] == null) {
            redirect('auth');
        }

        $data['title'] = 'Homepage | DHC System';
        $data['user'] = $this->m_user->user($this->session->userdata['email']);
        $data['jml_pelanggan'] = $this->db->count_all('costumer');

        $this->db->select_sum('jumlah');
        $total = $this->db->get('cashout')->result_array();
        $data['jml_transaksi'] = $total[0]['jumlah'];

        $this->db->select_sum('total');
        $TOT = $this->db->get('transaksi')->result_array();
        $dataawal = $TOT[0]['total'];

        $this->db->select_sum('jumlah');
        $tota2 = $this->db->get('cashout')->result_array();
        $dataakhir = $tota2[0]['jumlah'];

        $data['list'] = $this->db->get('barang')->result_array();

        $this->db->limit(1);
        $this->db->order_by('id_riwayat', 'DESC');
        $data['jml_barang_habis'] = $this->db->get('riwayat')->row_array();

        $this->db->limit(5);
        $this->db->order_by('jml_kunjungan', 'DESC');
        $data['top_costumer'] = $this->db->get('costumer')->result_array();

        $date = new DateTime("now");
        $curdate = $date->format('Y-m-d');
        $this->db->where('DATE(timestamp)', $curdate);
        $this->db->select_sum('total');
        $total = $this->db->get('transaksi')->result_array();
        $data['jml_barang'] = $total[0]['total'];

        $this->load->view('user/template/header', $data);
        $this->load->view('user/template/navbar', $data);
        $this->load->view('user/content/bot', $data);
        $this->load->view('user/template/footer', $data);
    }

    public function lihat_pelanggan()
    {
        if ($this->session->userdata['email'] == null) {
            redirect('auth');
        }
        $data['title'] = 'Data Pelanggan | DHC System';
        $data['user'] = $this->m_user->user($this->session->userdata['email']);
        $data['list'] = $this->db->get('costumer')->result_array();

        $this->load->view('user/template/header', $data);
        $this->load->view('user/template/navbar', $data);
        $this->load->view('user/content/lihat_pelanggan', $data);
        $this->load->view('user/template/footer', $data);
    }

    public function tambah_pelanggan()
    {
        $this->form_validation->set_rules('nama', 'Nama', 'required|trim');
        $this->form_validation->set_rules('no', 'No', 'required');
        $this->form_validation->set_rules('asal', 'Asal', 'required|trim');

        $insert = [
            'nama_costumer' => $this->input->post('nama'),
            'no_hp' => htmlspecialchars($this->input->post('no')),
            'asal' => htmlspecialchars($this->input->post('asal'))
        ];

        $this->db->insert('costumer', $insert);
        $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Berhasil Mendaftarkan Pelanggan Baru</div>');
        redirect('user/lihat_pelanggan');
    }

    public function hapus_pelanggan($id)
    {
        $this->db->where('id_costumer', $id);
        $this->db->delete('costumer');
        redirect('user/lihat_pelanggan');
    }

    public function cashout()
    {
        if ($this->session->userdata['email'] == null) {
            redirect('auth');
        }
        $this->form_validation->set_rules('jumlah', 'Jumlah', 'required');
        $this->form_validation->set_rules('keterangan', 'Keterangan', 'required|trim');

        $data['title'] = 'Pengeluaran | DHC System';
        $data['user'] = $this->m_user->user($this->session->userdata['email']);
        $data['cashout'] = $this->db->get('cashout')->result_array();

        $this->db->select_sum('total');
        $this->db->where('jenis', 1);
        $TOT = $this->db->get('riwayat')->result_array();
        $dataawal = $TOT[0]['total'];

        $this->db->select_sum('total');
        $this->db->where('jenis', 2);
        $tota2 = $this->db->get('riwayat')->result_array();
        $dataakhir = $tota2[0]['total'];

        $data['sisakas'] = $dataawal - $dataakhir;

        $this->db->select_sum('jumlah');
        $totall = $this->db->get('cashout')->result_array();
        $data['totpengeluaran'] = $totall[0]['jumlah'];

        if ($this->form_validation->run() == false) {
            $this->load->view('user/template/header', $data);
            $this->load->view('user/template/navbar', $data);
            $this->load->view('user/content/cashout', $data);
            $this->load->view('user/template/footer', $data);
        } else {
            $this->db->order_by('id_riwayat', 'DESC');
            $this->db->limit(1);
            $omzet = $this->db->get('riwayat')->row_array();

            $insert = [
                'timestamp' => date('Y-m-d H:i:s', time()),
                'actor' => $data['user']['nama'],
                'keterangan' => htmlspecialchars($this->input->post('keterangan')),
                'jumlah' => $this->input->post('jumlah')
            ];
            $this->db->insert('cashout', $insert);

            $insertriwayat = [
                'jenis' => 2,
                'keterangan' => $insert['keterangan'],
                'jumlah' => 0,
                'total' => $insert['jumlah'],
                'omzet' => intval($omzet['omzet']) - $insert['jumlah']
            ];
            $this->db->insert('riwayat', $insertriwayat);

            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Berhasil Menambahkan Pengeluaran Baru</div>');
            redirect('user/cashout');
        }
    }
}
