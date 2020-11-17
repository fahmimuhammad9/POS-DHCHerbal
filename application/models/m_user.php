<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class M_user extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }

    function user($email)
    {
        $this->db->where('email', $email);
        return $this->db->get('user')->row_array();
    }
    function pemb($email)
    {
        if ($this->session->userdata['role_id'] == 2) {
            $this->db->join('pemb_industri', 'pemb_industri.email = usr.email');
            return $this->db->get_where('usr', ['usr.email' => $email])->row_array();
        } else {
            $this->db->join('pemb_prodi', 'pemb_prodi.email = usr.email');
            return $this->db->get_where('usr', ['usr.email' => $email])->row_array();
        }
    }

    function log_mhs_bimb()
    {
        $this->db->join('mahasiswa', 'mahasiswa.email = act_log.user');
        $this->db->join('usr', 'usr.email = mahasiswa.email');
        $this->db->where('mahasiswa.pembimbing_prodi', $this->session->userdata['email']);
        $this->db->where('act_log.detail', 'ABS');
        $this->db->order_by('act_log.timestamp', 'ASC');
        return $this->db->get('act_log');
    }
    function count_nm()
    {
        $this->db->where('status_mail', 1);
        $this->db->where('receiver', $this->session->userdata['email']);
        return $this->db->count_all_results('ml_listmail');
    }
    function notif_message()
    {
        $this->db->join('usr', 'usr.email = ml_listmail.sender');
        $this->db->where('status_mail', 1);
        $this->db->where('receiver', $this->session->userdata['email']);
        $this->db->order_by('timestamp', 'ASC');
        return $this->db->get('ml_listmail');
    }

    function search_receiver($name)
    {
        $this->db->like('nama', $name, 'both');
        $this->db->order_by('nama', 'ASC');
        $this->db->limit(10);
        return $this->db->get('usr')->result();
    }

    function log($email)
    {
        date_default_timezone_set("Asia/Jakarta");
        $timestamp = date('H:i:s');

        $data = [
            'timestamp' => $timestamp,
            'user' => $email,
            'act' => 'Bertanya',
            'detail' => 'Waktu Magang'
        ];

        $this->db->insert('act_log', $data);
    }
    function get_log_dosen()
    {
        $this->db->join('usr', 'usr.email = act_log.user');
        $this->db->join('pemb_prodi', 'pemb_prodi.email = usr.email');
        $this->db->limit(7);
        return $this->db->get_where('act_log', ['user' => $this->session->userdata['email']]);
    }
    function get_log_for_profile($email)
    {
        $this->db->join('usr m', 'm.email = c.user');
        $this->db->join('mahasiswa g', 'g.email = c.user');
        $this->db->order_by('c.timestamp', 'DESC');
        $this->db->limit(7);
        return $this->db->get_where('act_log c', ['c.user' => $email]);
    }

    function _uploadImage()
    {
        $config['upload_path']          = './assets/img/forum';
        $config['allowed_types']        = 'gif|jpg|png';
        $config['file_name']            = $this->id_judul;
        $config['overwrite']            = true;
        $config['max_size']             = 1024; // 1MB
        // $config['max_width']            = 1024;
        // $config['max_height']           = 768;

        $this->load->library('upload', $config);

        if ($this->upload->do_upload('gambar')) {
            return $this->upload->data("file_name");
        }

        return "default.jpg";
    }

    function count_rating_absensi($email)
    {
        $divide = $this->db->get_where('absensi', ['email' => $email])->num_rows();
        $upvide = $this->db->get_where('absensi', [
            'email' => $email,
            'kt_msk' => 'TEPAT WAKTU',
        ])->num_rows();

        if ($divide != 0) {
            $var = ($upvide / $divide) * 100;
        } else {
            $var = 'N/A';
        }
        return $var;
    }

    function absen_masuk($jm_msk_std)
    {
        $msk_std = strtotime($jm_msk_std);
        $jam_msk_rl = time();

        $diff = $msk_std - $jam_msk_rl;

        if ($diff < 0) {
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Berhasil Absen Tepat Waktu!</div>');
            return 'TEPAT WAKTU';
        } else {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Sayang, Anda Terlambat!</div>');
            return 'TERLAMBAT';
        }
    }

    function absen_pulang($jm_klr_std)
    {
        $klr_std = strtotime($jm_klr_std);
        $jam_klr_rl = time();

        $diff = $klr_std - $jam_klr_rl;

        if ($diff < 0) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Sayang, Anda Terlambat!</div>');
            return 'TERLAMBAT';
        } else {
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Berhasil Absen Tepat Waktu!</div>');
            return 'TEPAT WAKTU';
        }
    }

    function abs()
    {
        $this->db->join('mahasiswa', 'mahasiswa.email = usr.email');
        $this->db->join('perusahaan', 'perusahaan.id = mahasiswa.perusahaan');
        return $this->db->get_where('usr', ['usr.email' => $this->session->userdata['email']])->row_array();
    }
    function add_log_absensi($ket, $stat)
    {
        $data = [
            'timestamp' => time(),
            'user' => $this->session->userdata['email'],
            'act' => 'Absen ' . $ket . ' ' . $stat,
            'detail' => 'ABS'
        ];

        $this->db->insert('act_log', $data);
    }

    function get_all_forum()
    {
        $this->db->join('usr m', 'm.email = f.publisher');
        return $this->db->get('forum f');
    }

    function get_detail_forum($id)
    {
        $this->db->join('usr m', 'm.email = f.publisher');
        $this->db->join('role r', 'r.role_id = m.role_id');
        return $this->db->get_where('forum f', ['id_forum' => $id])->row_array();
    }

    function get_receiver()
    {
        $this->db->join('role r', 'r.role_id = x.role_id');
        $this->db->order_by('r.role', 'ASC');
        $this->db->order_by('x.nama', 'ASC');
        return $this->db->get('usr x');
    }

    function get_tot_conv($actor_1, $actor_2)
    {
        $this->db->where('actor_1', $actor_1);
        $this->db->where('actor_2', $actor_2);
        $val = $this->db->count_all_results('ml_conv_room');

        if ($val > 0) {
            return $val;
        } else {
            $this->db->where('actor_1', $actor_2);
            $this->db->where('actor_2', $actor_1);
            return $this->db->count_all_results('ml_conv_room');
        }
    }
    function tot_conv($actor_1, $actor_2)
    {
        $this->db->where('actor_1', $actor_1);
        $this->db->where('actor_2', $actor_2);
        $val = $this->db->get('ml_conv_room')->row_array();

        if ($val > 0) {
            return $val;
        } else {
            $this->db->where('actor_1', $actor_2);
            $this->db->where('actor_2', $actor_1);
            return $this->db->get('ml_conv_room')->row_array();
        }
    }
    function get_trash()
    {
        $this->db->where('status_mail', 4);
        $this->db->where('sender', $this->session->userdata['email']);
        $sender = $this->db->count_all_results('ml_listmail');

        if ($sender != 0) {
            $this->db->order_by('status_mail', 'ASC');
            $this->db->order_by('timestamp', 'DESC');
            $this->db->join('usr', 'usr.email = ml_listmail.sender');
            $this->db->where('sender', $this->session->userdata['email']);
            $this->db->where('status_mail', 4);
            return $this->db->get('ml_listmail');
        } else {
            $this->db->order_by('status_mail', 'ASC');
            $this->db->order_by('timestamp', 'DESC');
            $this->db->join('usr', 'usr.email = ml_listmail.receiver');
            $this->db->where('receiver', $this->session->userdata['email']);
            $this->db->where('status_mail', 4);
            return $this->db->get('ml_listmail');
        }
    }
    function get_comment($id)
    {
        $this->db->join('usr v', 'v.email = fc.commenter');
        $this->db->join('role r', 'r.role_id = v.role_id');
        $this->db->order_by('timestamp', 'ASC');
        return $this->db->get_where('f_comment fc', ['fc.id_forum' => $id]);
    }
    function get_like($id)
    {
        return $this->db->get_where('f_like ft', ['ft.id_diskus' => $id, 'ft.liker' => $this->session->userdata['email']])->row_array();
    }
    function get_message()
    {
        $this->db->order_by('status_mail', 'ASC');
        $this->db->order_by('timestamp', 'DESC');
        $this->db->join('ml_mailstat', 'ml_mailstat.id_stat = ml_listmail.status_mail');
        $this->db->join('usr', 'usr.email = ml_listmail.sender');
        $this->db->where('ml_listmail.receiver', $this->session->userdata['email']);
        $this->db->where('status_mail', 1);
        $this->db->or_where('status_mail', 2);
        return $this->db->get('ml_listmail');
    }
    function get_message_sent()
    {
        $this->db->order_by('timestamp', 'DESC');
        $this->db->join('usr', 'usr.email = ml_listmail.receiver');
        return $this->db->get_where('ml_listmail', ['sender' => $this->session->userdata['email']]);
    }
    function get_message_arsip()
    {
        $this->db->order_by('timestamp', 'DESC');
        $this->db->join('usr', 'usr.email = ml_listmail.receiver');
        $this->db->where('status_mail', 3);
        $this->db->where('receiver', $this->session->userdata['email']);
        $this->db->or_group_start();
        $this->db->where('status_mail', 3);
        $this->db->where('sender', $this->session->userdata['email']);
        $this->db->group_end();
        return $this->db->get('ml_listmail');
    }
    function bimb()
    {
        $this->db->join('usr', 'usr.email = mahasiswa.email');
        $this->db->join('perusahaan', 'perusahaan.id = mahasiswa.perusahaan');
        $this->db->order_by('nim', 'ASC');
        $this->db->where('pembimbing_prodi', $this->session->userdata['email']);
        return $this->db->get('mahasiswa');
    }
}
