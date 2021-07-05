<?php

namespace App\Core\Clients\Controllers;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Laravel\Passport\ClientRepository;

class ExternalClientController extends ApiController
{
    /**
     * @var ClientRepository
     */
    private $clientRepository;

    public function __construct(ClientRepository $clientRepository)
    {
        $this->middleware('check.external_access');
        $this->clientRepository = $clientRepository;
    }

    public function getCredentialsExternalClient(Request $request)
    {
        $data[ 'success' ] = true;


        return $this->successResponse($data, 200);
    }
}
