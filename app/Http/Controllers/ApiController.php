<?php

namespace App\Http\Controllers;

use App\Http\Helpers\InfusionsoftHelper;
use Illuminate\Http\Request;
use App\Tag;
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
        //Grab POST parameter   
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


        //
        $contact = $this->checkIfExistsInInfusionsoft($email);
        //Check if email exists in infusionsoft
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

            //Get Tag Id of the module reminder 
            $tagId = $this->getTagId($email, $contactProducts);

            $infusionsoft = new InfusionsoftHelper();
            $infusionsoft->addTag($contactId, $tagId);
            return Response::json([
                "success" => true,
                "message" => "Contact: " . $contactId . " Has had the tag: " . $tagId ." attached to their account."
            ]);

        }

    }

    /**
     * Return the boolean or array for email address check for valid input
     * 
     * @param string
     * @return bool
     */
    private function checkIfInputGiven($email)
    {
        if (!$email) {
            return false;
        }
        else if($email){
            return true;
        }
    }

    /**
     * Return the boolean or array for email address check for valid email
     * 
     * @param string
     * @return bool
     */
    private function checkForValidEmail($email)
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return true;
          } else {
            return false;
          }
    }
    
    /**
     * Return the boolean or array for email address check in infusionsoft
     * 
     * @param string
     * @return bool|array
     */
    private function checkIfExistsInInfusionsoft($email)
    {
        $infusionsoft = new InfusionsoftHelper();
        $contact = $infusionsoft->getContact($email);
        if ((response()->json($contact)) === false) {
            return false;
        }else{
            return $contact;
        }
    }

    /**
     * Return the tag id for the module reminder
     * 
     * @param string, string
     * @return int
     */
    private function getTagId($email, $products){

        $lastModules = array('IPA Module 7', 'IEA Module 7', 'IAA Module 7');

        $user = User::where('email', '=', $email)->first();

        // Get last completed module(based off purchase order)
        $lastCompletedModule = $this->getLastCompletedModule($user, $products);

        if (empty($lastCompletedModule)){
            //User hasn't started yet
            $nextModuleName = $this->getFirstModuleName($products);
        } 
        elseif ($this->completedAllModules($lastCompletedModule, $products)) { 
            // User completed all purchased last modules
            $nextModuleName = 'completed';
        } 
        else {
            if (in_array($lastCompletedModule->name, $lastModules))
            { 
                // Check if it is the last module in the course
                $nextModuleName = $this->getNextCourseModuleName($lastCompletedModule, $products);
            } else 
            { // This is not the last module
                $nextModuleName = $this->getNextModuleName($lastCompletedModule);
            }
        }
        
        $tag_id = $this->getTagIdFromName($nextModuleName);

        return $tag_id;
    }

    /**
     * Return the most recent completed module 
     * 
     * @param object, string
     * @return object
     */
    private function getLastCompletedModule($user, $products){

        // Iterate through object from last to first to find out lasts completed module
        $products = array_reverse(explode(',', $products));
        foreach ($products as $product){
            $lastCompletedModule = $user->completed_modules()->where('course_key', '=', $product)->orderBy('id', 'desc')->first();
            if (!empty($lastCompletedModule)){
                break;
            }
        }
        return $lastCompletedModule;
    }

    /**
     * Grab first module name based off customer products
     * 
     * @param string
     * @return int
     */

    private function getFirstModuleName($products){

        $products = explode(',', $products);
        $nextModuleName = strtoupper($products[0]) . ' Module 1';
        return $nextModuleName;
    }

    /**
     * Return true if all modules complete, false for anything else.
     * @param object, string
     * @return boolean
     */

    private function completedAllModules($lastUserModule, $products){

        $products = explode(',', $products);
        $lastModuleName = strtoupper(end($products)) . ' Module 7';
        
        if ($lastUserModule->name === $lastModuleName){
            $boolean = true;
        } else {
            $boolean = false;
        }

        return $boolean;
    }

    /**
     * Return the module name in the next course
     * 
     * @param string,string
     * @return string
     */

    private function getNextCourseModuleName($lastModuleForUser, $products){
        
        $products = explode(',', $products);
        foreach ($products as $index => $product){
            if ($product === $lastModuleForUser->course_key){
                $next_index = $index + 1;
                try {
                    $nextCourse = $products[$next_index];
                    break;
                }
                catch (\Exception $e) {
                    return $e->getMessage();
                }
            }
        }

        $nextModuleName = strtoupper($nextCourse) . ' Module 1';

        return $nextModuleName;
    }

    /**
     * Return next module name
     * 
     * @param object
     * @return string
     */

    private function getNextModuleName($lastUserModule){

        $nameArray = explode(' ', $lastUserModule->name);
        $nameArray[2] = (int)$nameArray[2] + 1;
        $nextModuleName = implode(' ', $nameArray);
        
        return $nextModuleName;
    }

    /**
     * Return the Tag Id from the name of the given module
     * 
     * @param string
     * @return int
     */

    private function getTagIdFromName($moduleName){

        if ($moduleName === 'completed')
        {
            $moduleReminderName = 'Module reminders completed';
        } 
        else 
        {
            $moduleReminderName = 'Start ' . $moduleName . ' Reminders';
        }
        
        $tag = Tag::where('name', '=', $moduleReminderName)->firstOrFail();
        
        return $tag['id'];
    }

}
