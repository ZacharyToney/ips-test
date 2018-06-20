<?php

namespace App\Http\Controllers;

use App\Http\Helpers\InfusionsoftHelper;
use Illuminate\Http\Request;
use App\User;
use App\Module;
use Response;

class ApiController extends Controller
{
    // Todo: Module reminder assigner

    private function exampleCustomer(){

        $infusionsoft = new InfusionsoftHelper();

        $uniqid = uniqid();

        $infusionsoft->createContact([
            'Email' => $uniqid.'@test.com',
            "_Products" => 'ipa,iea'
        ]);

        $user = User::create([
            'name' => 'Test ' . $uniqid,
            'email' => $uniqid.'@test.com',
            'password' => bcrypt($uniqid)
        ]);

        // attach IPA M1-3 & M5
        $user->completed_modules()->attach(Module::where('course_key', 'ipa')->limit(3)->get());
        $user->completed_modules()->attach(Module::where('name', 'IPA Module 5')->first());


        return $user;
    }

    public function moduleReminderAssigner(Request $request)
    {
        $infusionsoft = new InfusionsoftHelper();
        
        $email = $request->input('contact_email');

        //Check if user put in an input
        if ($this->checkIfInputGiven($email) === false) {
            return Response::json([
                "success" => false,
                "message" => 'No input given.'
            ]);
        }

        //Check if valid email
        if ($this->checkForValidEmail($email) === false) {
            return Response::json([
                "success" => false,
                "message" => 'Invalid email given.'
            ]);
        }


        //Check if email exists in infusionsoft
        $contact = $this->checkIfExistsInInfusionsoft($email);
        if ($contact === false) {
            //Doesn't exist in infusionsoft
            return Response::json([
                "success" => false,
                "message" => 'Invalid email given, does not exist inside infusionsoft.'
            ]);
        }
        else{

            //Exists in infusionsoft
            $contactId = $contact['Id'];
            $contactProducts = $contact['_Products'];
        }

    }

    private function checkIfInputGiven($email)
    {
        if (!$email) {
            return false;
        }
        else if($email){
            return true;
        }
    }

    private function checkForValidEmail($email)
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return true;
          } else {
            return false;
          }
    }
    
    private function checkIfExistsInInfusionsoft($email)
    {
        return response()->json($infusionsoft->getContact($email));
    }
}
