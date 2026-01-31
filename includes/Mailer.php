<?php

class Mailer {
    private $host;
    private $port;
    private $username;
    private $password;
    private $fromEmail;
    private $fromName;

    public function __construct() {
        // Fetch SMTP settings from database
        $this->host = getSetting('smtp_host') ?: 'smtp.gmail.com';
        $this->port = getSetting('smtp_port') ?: 465;
        $this->username = getSetting('smtp_email');
        $this->password = getSetting('smtp_password');
        $this->fromEmail = $this->username;
        $this->fromName = getSetting('site_name') ?: 'Zoonacart';
    }

    public function send($to, $subject, $body) {
        if (!$this->username || !$this->password) {
            error_log("SMTP Credentials missing.");
            return false;
        }

        // Simple SMTP implementation using sockets
        try {
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ]);

            $socket = stream_socket_client("ssl://{$this->host}:{$this->port}", $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $context);
            
            if (!$socket) throw new Exception("Connection failed: $errno $errstr");

            $this->logServerResponse($socket, "220");
            $this->sendCommand($socket, "EHLO zoonacart.com");
            $this->logServerResponse($socket, "250");
            
            $this->sendCommand($socket, "AUTH LOGIN");
            $this->logServerResponse($socket, "334");
            
            $this->sendCommand($socket, base64_encode($this->username));
            $this->logServerResponse($socket, "334");
            
            $this->sendCommand($socket, base64_encode($this->password));
            $this->logServerResponse($socket, "235");
            
            $this->sendCommand($socket, "MAIL FROM: <{$this->fromEmail}>");
            $this->logServerResponse($socket, "250");
            
            $this->sendCommand($socket, "RCPT TO: <$to>");
            $this->logServerResponse($socket, "250");
            
            $this->sendCommand($socket, "DATA");
            $this->logServerResponse($socket, "354");

            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "From: {$this->fromName} <{$this->fromEmail}>\r\n";
            $headers .= "To: $to\r\n";
            $headers .= "Subject: $subject\r\n";
            $headers .= "Date: " . date('r') . "\r\n";

            fwrite($socket, "$headers\r\n$body\r\n.\r\n");
            $this->logServerResponse($socket, "250");
            
            $this->sendCommand($socket, "QUIT");
            fclose($socket);
            
            return true;
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            error_log("Mail Error: " . $e->getMessage());
            return false;
        }
    }

    private $lastError;
    public function getLastError() { return $this->lastError; }

    private function sendCommand($socket, $cmd) {
        fwrite($socket, $cmd . "\r\n");
    }

    private function logServerResponse($socket, $expect) {
        $response = "";
        while ($line = fgets($socket, 512)) {
            $response .= $line;
            if (substr($line, 3, 1) == " ") break;
        }
        if (substr($response, 0, 3) != $expect) {
            throw new Exception("SMTP Error: Expected $expect, got $response");
        }
    }
}
?>
