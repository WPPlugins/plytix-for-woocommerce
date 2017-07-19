<?php

/**
 * Credentials Service Endpoint
 * Client to check if credentials are valid.
 *
 * Class CredentialService
 */
class CredentialService extends BaseService {

    /**
     * Pings to Plytix API to check if our credentials are valid.
     *
     * @return int
     */
    public function checkCredentials()
    {
        $valid = 1;
        try {
            parent::_ping();
        } catch (Exception $e) {
            $valid = 0;
        }
        return $valid;
    }
}
