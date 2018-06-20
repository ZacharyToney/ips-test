<?php

namespace App\Http\Helpers;

use Infusionsoft;
use Log;
use Storage;
use Request;


class InfusionsoftHelper
{
    private $infusionsoft;

    public function __construct()
    {
        if (Storage::exists('inf_token')) {

            Infusionsoft::setToken(unserialize(Storage::get("inf_token")));

        } else {
            Log::error("Infusionsoft token not set.");
        }
    }

    public function authorize(){
        if (Request::has('code')) {
            Infusionsoft::requestAccessToken(Request::get('code'));

            Storage::put('inf_token', serialize(Infusionsoft::getToken()));
            Log::notice('Infusionsoft token created');

            Infusionsoft::setToken(unserialize(Storage::get("inf_token")));

            return 'Success';
        }

        return '<a href="' . Infusionsoft::getAuthorizationUrl() . '">Authorize Infusionsoft</a>';
    }

    public function getAllTags(){
        try {

            return Infusionsoft::tags()->all();

        } catch (\Exception $e){
            Log::error((string) $e);
            return false;
        }
    }

    public function getContact($email)
    {

        $fields = [
            'Id',
            'Email',
            'Groups',
            "_Products"
        ];

        try {

            return Infusionsoft::contacts('xml')->findByEmail($email, $fields)[0];

        } catch (\Exception $e){
            Log::error((string) $e);
            return false;
        }
    }

    public function addTag($contact_id, $tag_id){
        try {
            return Infusionsoft::contacts('xml')->addToGroup($contact_id, $tag_id);

        } catch (\Exception $e){
            Log::error((string) $e);
            return false;
        }
    }

    public function createContact($data){

        try {
            return Infusionsoft::contacts('xml')->add($data);

        } catch (\Exception $e){
            Log::error((string) $e);
            return false;
        }
    }


}
