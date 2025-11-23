<?php

namespace App\Services;

use RouterOS\Client;
use RouterOS\Query;
use RouterOS\Exceptions\ConnectException;
use RouterOS\Exceptions\ClientException;

class MikrotikService
{
    protected $client;

    public function __construct()
    {
        try {
            // Inisialisasi client dengan config dari .env
            $this->client = new Client([
                'host' => env('MIKROTIK_HOST'),
                'user' => env('MIKROTIK_USER'),
                'pass' => env('MIKROTIK_PASS'),
                'port' => (int) env('MIKROTIK_PORT'),
                'timeout' => 10, // Detik
            ]);
        } catch (ConnectException | ClientException $e) {
            // Kita biarkan null jika gagal, nanti dicek di Controller
            $this->client = null;
        }
    }

    // Cek status koneksi
    public function isConnected()
    {
        return $this->client !== null;
    }

    // Ambil daftar user yang sedang Online (Active)
    public function getActiveUsers()
    {
        if (!$this->isConnected()) return [];

        // /ppp/active/print
        $query = new Query('/ppp/active/print');
        return $this->client->query($query)->read();
    }

    // Ambil daftar semua user terdaftar (Secret)
    public function getSecrets()
    {
        if (!$this->isConnected()) return [];

        // /ppp/secret/print
        $query = new Query('/ppp/secret/print');
        return $this->client->query($query)->read();
    }

    // Logic untuk memutus koneksi user
    public function kickUser($username)
    {
        if (!$this->isConnected()) return false;

        // 1. Cari ID koneksi aktif berdasarkan nama user
        $queryFind = (new Query('/ppp/active/print'))
            ->where('name', $username);

        $activeUser = $this->client->query($queryFind)->read();

        // Jika user ditemukan sedang online
        if (!empty($activeUser)) {
            // Ambil .id (contoh: *1A)
            $id = $activeUser[0]['.id'];

            // 2. Eksekusi perintah remove
            $queryKick = (new Query('/ppp/active/remove'))
                ->equal('.id', $id);

            $this->client->query($queryKick)->read();
            return true;
        }

        return false; // User tidak sedang online
    }

    // ... kode sebelumnya ...

    // Fungsi untuk Mengubah Status Secret (Enable/Disable)
    public function setSecretStatus($username, $status = 'disabled') // status: 'disabled' atau 'enabled'
    {
        if (!$this->isConnected()) return false;

        // 1. Cari ID Secret berdasarkan username
        $queryFind = (new Query('/ppp/secret/print'))
            ->where('name', $username);
        $secret = $this->client->query($queryFind)->read();

        if (!empty($secret)) {
            $id = $secret[0]['.id'];
            $value = ($status === 'disabled') ? 'yes' : 'no';

            // 2. Set disabled=yes/no
            $querySet = (new Query('/ppp/secret/set'))
                ->equal('.id', $id)
                ->equal('disabled', $value);

            $this->client->query($querySet)->read();
            return true;
        }
        return false;
    }

    // Ambil daftar Profile PPPoE (untuk Dropdown)
    public function getProfiles()
    {
        if (!$this->isConnected()) return [];
        $query = new Query('/ppp/profile/print');
        return $this->client->query($query)->read();
    }

    // Tambah User Baru ke Mikrotik
    public function addSecret($data)
    {
        if (!$this->isConnected()) return false;

        $query = (new Query('/ppp/secret/add'))
            ->equal('name', $data['username'])
            ->equal('password', $data['password'])
            ->equal('service', 'pppoe')
            ->equal('profile', $data['profile'])
            ->equal('comment', $data['comment'] ?? '');

        $this->client->query($query)->read();
        return true;
    }

    // Hapus User dari Mikrotik
    public function removeSecret($username)
    {
        if (!$this->isConnected()) return false;

        // Cari ID dulu
        $queryFind = (new Query('/ppp/secret/print'))->where('name', $username);
        $user = $this->client->query($queryFind)->read();

        if (!empty($user)) {
            $id = $user[0]['.id'];
            $queryRemove = (new Query('/ppp/secret/remove'))->equal('.id', $id);
            $this->client->query($queryRemove)->read();
            return true;
        }
        return false;
    }
}
