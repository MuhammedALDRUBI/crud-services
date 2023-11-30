
# Overview :
All storing services can store models and handling their files and relationships
(it can even handle the files and the relationships of the model's relationships) .

## StoringServices main types :
- SingleRowStoringService
    - Stores a single model .
    - Expects one data array contains the model keys and its relationships keys (if exists).

- MultiRowStoringService
    - Stores multiple models at the same time .
    - Expects to receive an array of data arrays (each sub array presents a model's data array) , This point is important during preparing the request form classes
  and the data comes from frontend site.

<hr>

## Usage

#### Step 1 : Creating a new storing service :
- create a new class and make it extend one of these storing service types :
  - App\CustomLibs\CRUDServices\CRUDServiceTypes\DataWriterCRUDServices\StoringServices\SingleRowStoringService .
  - App\CustomLibs\CRUDServices\CRUDServiceTypes\DataWriterCRUDServices\StoringServices\MultiRowStoringService.


#### Step 2 : Defining the required methods in the newly created child StoringService class :
- protected function getRequestClass(): string
- protected function getModelClass(): string
- protected function getModelCreatingFailingErrorMessage(): string
- protected function getModelCreatingSuccessMessage(): string

#### Step 3 : create an instance of the newly created service and use the public method 'create' .

Follow up the steps bellow to prepare the module to be able to use by StoringService .

<hr>

#### Preparing the model :
- Make sure to add the columns will be filled to the <b>fillable property.</b>
- Make sure to add table name to <b>table property </b>.
- If the model has any relationship or files look at their docs part to handle them .

#### Preparing Request form class :
- Create a new class and make it extend App\Http\Requests\BaseFormRequest class .
- rules method : Add the validation rules those are not related to checking data in database or cache or any data resource ... just add the rules check the data form and data type .
- If there is need to define rules related to files use the custom rules :
  App\CustomLibs\CRUDServices\ValidationManagers\CRUDValidationRules\FileValidationRules\SingleFileOrSinglePathString , App\CustomLibs\CRUDServices\ValidationManagers\CRUDValidationRules\FileValidationRules\MultiFileOrMultiPathString ( look at files handling docs part ).
Ex :

      public function rules(array $data)
      {
          return [ "name" => ["required" , "string" ,"max:55" ]  ]
      }

- getModelKeyAdvancedValidationRules method : If there is any need to check column's value in a data resource like cache or database follow these steps :
    - make the Request class implement App\CustomLibs\CRUDServices\Interfaces\ValidationManagerInterfaces\NeedsModelKeyAdvancedValidation and implement the method
      public function getModelKeyAdvancedValidationRules(array $data = []) : array .
    - getModelKeyAdvancedValidationRules method must return an array of rules that can be applied in a data resource like database .
    - use $data array in rules and getModelKeyAdvancedValidationRules methods to use request data values during defining the rules .
    - Ex :

                public function getModelKeyAdvancedValidationRules(array $data = []) : array
                {
                    return [
                              "name" => ["nullable" , "unique:users,name"]
                           ];
                }

#### using the policies to authorize the storing action :
- As you know ... Nobody should be able to store something in the system if he hasn't the permissions those allow him to perform this storing action .
- Two ways to do that : and they must return true or false .... define the condition must be true to allow the user to perform this storing action (You should use a policy calling here).
    - App\Policies\BasePolicy::check method : to call a policy action for the logged user .
  1 - Using CRUDService 's AuthorizeByPolicy method (returns true or false ).
  2 - Using Request form class 's public method 'authorize' (returns true or false ).
    Ex :

- in CRUDService child class :

          protected function AuthorizeByPolicy(): bool
          {
            return BasePolicy::check('create', ControlPanel::class);
          }
- in Request Form class 

          protected function authorize(): bool
          {
            return BasePolicy::check('create', ControlPanel::class);
          }
   
