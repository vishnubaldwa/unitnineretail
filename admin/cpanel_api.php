<?php
/**
 * cPanel UAPI Wrapper for Email Management
 */
defined('UNR_ADMIN') or define('UNR_ADMIN', true);

class CpanelAPI {
    private $host;
    private $port;
    private $user;
    private $token;

    public function __construct($host, $port, $user, $token) {
        $this->host  = $host ?: '127.0.0.1';
        $this->port  = $port ?: 2083;
        $this->user  = $user;
        $this->token = $token;
    }

    /**
     * Generic cPanel UAPI caller
     */
    public function call($module, $function, $params = [], $method = 'GET') {
        $url = "https://{$this->host}:{$this->port}/execute/{$module}/{$function}";
        
        $ch = curl_init();
        $headers = [
            "Authorization: cpanel {$this->user}:{$this->token}",
            "Accept: application/json"
        ];

        if (strtoupper($method) === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        } else {
            if (!empty($params)) {
                $url .= '?' . http_build_query($params);
            }
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($err) {
            return [
                'status' => 0,
                'errors' => ["Connection failed: " . $err],
                'data'   => null
            ];
        }

        $decoded = json_decode($response, true);
        if ($decoded === null) {
            return [
                'status' => 0,
                'errors' => ["Invalid response from cPanel server (HTTP {$httpCode})"],
                'data'   => null,
                'raw'    => $response
            ];
        }

        return $decoded;
    }

    /**
     * List all email accounts with disk usage
     */
    public function listAccounts() {
        return $this->call('Email', 'list_pops_with_disk');
    }

    /**
     * Create a new POP/IMAP email account
     */
    public function createAccount($email, $password, $quota = 0, $domain = '') {
        $params = [
            'email'    => $email,
            'password' => $password,
            'quota'    => intval($quota),
            'domain'   => $domain
        ];
        return $this->call('Email', 'add_pop', $params, 'POST');
    }

    /**
     * Delete an email account
     */
    public function deleteAccount($email, $domain = '') {
        $params = [
            'email'  => $email,
            'domain' => $domain
        ];
        return $this->call('Email', 'delete_pop', $params, 'POST');
    }

    /**
     * Change password for an email account
     */
    public function changePassword($email, $password, $domain = '') {
        $params = [
            'email'    => $email,
            'password' => $password,
            'domain'   => $domain
        ];
        return $this->call('Email', 'passwd_pop', $params, 'POST');
    }

    /**
     * Edit mailbox disk quota (in MB, 0 = unlimited)
     */
    public function changeQuota($email, $quota, $domain = '') {
        $params = [
            'email'  => $email,
            'quota'  => intval($quota),
            'domain' => $domain
        ];
        return $this->call('Email', 'edit_pop_quota', $params, 'POST');
    }

    /**
     * Suspend incoming and outgoing
     */
    public function suspendAccount($email) {
        $in  = $this->call('Email', 'suspend_incoming', ['email' => $email], 'POST');
        $out = $this->call('Email', 'suspend_outgoing', ['email' => $email], 'POST');
        return [
            'status' => ($in['status'] && $out['status']) ? 1 : 0,
            'incoming' => $in,
            'outgoing' => $out
        ];
    }

    /**
     * Unsuspend incoming and outgoing
     */
    public function unsuspendAccount($email) {
        $in  = $this->call('Email', 'unsuspend_incoming', ['email' => $email], 'POST');
        $out = $this->call('Email', 'unsuspend_outgoing', ['email' => $email], 'POST');
        return [
            'status' => ($in['status'] && $out['status']) ? 1 : 0,
            'incoming' => $in,
            'outgoing' => $out
        ];
    }
}
