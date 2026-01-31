<?php

class Shiprocket {
    private $email;
    private $password;
    private $token;
    private $baseUrl = 'https://apiv2.shiprocket.in/v1/external';

    public function __construct() {
        // Fetch credentials from database settings
        $this->email = getSetting('shiprocket_email');
        $this->password = getSetting('shiprocket_password');
    }

    private function login() {
        if ($this->token) return $this->token;

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->baseUrl . '/auth/login',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode(['email' => $this->email, 'password' => $this->password]),
            CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        
        $data = json_decode($response, true);
        if (isset($data['token'])) {
            $this->token = $data['token'];
            return $this->token;
        }
        return false;
    }

    public function createOrder($orderData) {
        $token = $this->login();
        if (!$token) return ['error' => 'Authentication failed'];

        // Map Zoonacart order to Shiprocket format
        // This is a simplified mapping. In real scenario, complete address decomposition is needed.
        $payload = [
            'order_id' => $orderData['id'],
            'order_date' => date('Y-m-d H:i', strtotime($orderData['order_date'])),
            'pickup_location' => 'Primary', // Must be created in Shiprocket Dashboard
            'billing_customer_name' => $orderData['customer_name'],
            'billing_last_name' => '',
            'billing_address' => $orderData['delivery_address'],
            'billing_city' => 'New Delhi', // Placeholder: Should be parsed from address
            'billing_pincode' => '110001', // Placeholder
            'billing_state' => 'Delhi', // Placeholder
            'billing_country' => 'India',
            'billing_email' => 'customer@example.com', // Should be fetched from users table
            'billing_phone' => '9876543210', // Should be fetched from users table
            'shipping_is_billing' => true,
            'order_items' => []
        ];
        
        // Add items
        // $payload['order_items'][] = [ ... ] -> logic to be added if details available

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->baseUrl . '/orders/create/adhoc',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response, true);
    }

    public function trackOrder($awb) {
        // If no credentials, return Mock Data (Test Mode)
        if (!$this->email || !$this->password) {
            return [
                'tracking_data' => [
                    'track_status' => 1,
                    'shipment_status' => 7, // Delivered
                    'shipment_track_activities' => [
                        ['activity' => 'Delivered', 'date' => date('Y-m-d H:i:s'), 'location' => 'New Delhi']
                    ]
                ]
            ];
        }

        $token = $this->login();
        if (!$token) return false;

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->baseUrl . '/courier/track/awb/' . $awb,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response, true);
    }
}
?>
