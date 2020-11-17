<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class M_administrator extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }

    function user($email)
    {
        $this->db->join('admin', 'admin.email = usr.email');
        return $this->db->get_where('usr', ['usr.email' => $email])->row_array();
    }

    function add($table, $data)
    {
        $this->db->insert($table, $data);
    }

    function get_single($table)
    {
        return $this->db->get($table);
    }

    function get_where($table, $params)
    {
        return $this->db->get_where($table, $params);
    }

    function reset($table)
    {
        $this->db->truncate($table);
    }

    function delete($table, $params, $where)
    {
        $this->db->where($where, $params);
        $this->db->delete($table);
    }

    function show_mhs_all()
    {
        $this->db->where('role_id', 1);
        $this->db->join('mahasiswa m', 'm.email = x.email');
        $this->db->join('programstudi p', 'p.id = m.prodi');
        $this->db->join('kelas k', 'k.id = m.kelas');
        return $this->db->get('usr x');
    }

    function show_admin_all()
    {
        $this->db->join('pemb_industri', 'pemb_industri.email = usr.email');
        $this->db->join('perusahaan', 'perusahaan.id = pemb_industri.perusahaan');
        $this->db->join('role', 'role.role_id = usr.role_id');
        $this->db->where('usr.role_id', 2);
        return $this->db->get_where('usr');
    }

    function search_mhs($search)
    {
        $this->db->select('*');
        $this->db->from('mahasiswa');
        $this->db->like('nim', $search);
        $this->db->or_like('nama_mhs', $search);
        return $this->db->get();
    }

    function get_comment($id_forum)
    {
        $this->db->join('usr', 'usr.email = f_comment.commenter');
        return $this->db->get_where('f_comment', ['id_forum' => $id_forum]);
    }

    function get_like($id_forum)
    {
        return $this->db->get_where('f_like ft', ['ft.id_diskus' => $id_forum, 'ft.liker' => $this->session->userdata['email']])->row_array();
    }
    function intent_list($projectId)
    {
        // get intents
        $intentsClient = new IntentsClient();
        $parent = $intentsClient->projectAgentName($projectId);
        $intents = $intentsClient->listIntents($parent);
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
}
