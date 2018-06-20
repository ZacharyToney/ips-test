<?php

namespace App\Http\Controllers;

use App\Http\Helpers\InfusionsoftHelper;
use Request;
use Storage;
use Response;

class InfusionsoftController extends Controller
{
    public function authorizeInfusionsoft(){
        return (new InfusionsoftHelper())->authorize();
    }

    public function testInfusionsoftIntegrationGetEmail($email){

        $infusionsoft = new InfusionsoftHelper();

        return Response::json($infusionsoft->getContact($email));
    }

    public function testInfusionsoftIntegrationAddTag($contact_id, $tag_id){

        $infusionsoft = new InfusionsoftHelper();

        return Response::json($infusionsoft->addTag($contact_id, $tag_id));
    }

    public function testInfusionsoftIntegrationGetAllTags(){

        $infusionsoft = new InfusionsoftHelper();

        return Response::json($infusionsoft->getAllTags());
    }

    public function testInfusionsoftIntegrationCreateContact(){

        $infusionsoft = new InfusionsoftHelper();

        return Response::json($infusionsoft->createContact([
            'Email' => uniqid().'@test.com',
            "_Products" => 'ipa,iea'
        ]));
    }
}
