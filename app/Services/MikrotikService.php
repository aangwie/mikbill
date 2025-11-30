<?php

namespace App\Services;

use RouterOS\Client;
use RouterOS\Query;
use RouterOS\Exceptions\ConnectException;
use RouterOS\Exceptions\ClientException;
use App\Models\RouterSetting;

class MikrotikService
{
    protected $client;

    public function __construct()
    {
        try {
            // AMBIL YANG STATUSNYA AKTIF
            $config = RouterSetting::where('is_active', true)->first();

            // Jika tidak ada yang aktif, ambil yang pertama saja (fallback)
            if (!$config) {
                $config = RouterSetting::first();
            }

            if (!$config) {
                $this->client = null;
                return;
            }

            $this->client = new Client([
                'host' => $config->host,
                'user' => $config->username,
                'pass' => $config->password,
                'port' => (int) $config->port,
                'timeout' => 10,
            ]);
        } catch (ConnectException | ClientException $e) {
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

    // Ambil Daftar Interface (Ethernet/VLAN/Bridge/dll)
    public function getInterfaces()
    {
        if (!$this->isConnected()) return [];
        // Ambil hanya yang tipe ethernet atau bridge (opsional filter)
        // Disini kita ambil semua agar fleksibel
        $query = new Query('/interface/print');
        return $this->client->query($query)->read();
    }

    // Ambil Traffic Realtime (Monitor Traffic)
    public function getTraffic($interfaceName)
    {
        if (!$this->isConnected()) return ['rx' => 0, 'tx' => 0];

        // Perintah monitor-traffic dengan argumen 'once' agar tidak streaming
        $query = (new Query('/interface/monitor-traffic'))
            ->equal('interface', $interfaceName)
            ->equal('once');

        $result = $this->client->query($query)->read();

        if (!empty($result)) {
            return [
                'rx' => isset($result[0]['rx-bits-per-second']) ? $result[0]['rx-bits-per-second'] : 0,
                'tx' => isset($result[0]['tx-bits-per-second']) ? $result[0]['tx-bits-per-second'] : 0,
            ];
        }

        return ['rx' => 0, 'tx' => 0];
    }

    // Update Data Secret (Misal ganti Profile atau Password)
    public function updateSecret($username, $data)
    {
        if (!$this->isConnected()) return false;

        // 1. Cari ID Secret berdasarkan Username
        $queryFind = (new Query('/ppp/secret/print'))->where('name', $username);
        $user = $this->client->query($queryFind)->read();

        if (empty($user)) return false;

        $id = $user[0]['.id'];

        // 2. Lakukan Update (Set)
        // $data adalah array, misal: ['profile' => 'up-10mbps', 'password' => '123']
        $queryUpdate = (new Query('/ppp/secret/set'))->equal('.id', $id);
        
        foreach ($data as $key => $value) {
            $queryUpdate->equal($key, $value);
        }

        $this->client->query($queryUpdate)->read();
        return true;
    }
}
