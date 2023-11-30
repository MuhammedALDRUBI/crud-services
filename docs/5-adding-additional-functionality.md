## Hooks :

### Overview :
- Each CRUDService has some hooks to allow you to perform an extra functionality at specific points at the request life cycle .
- Some of these methods are defined in CRUDService main class and the other methods are defined in the main CRUDService types those need another custom hook methods ... When you need to use it you just need to override them from the 
child CRUDService (storing , updating or .... etc) , And there is no need to define them again if it is not necessary.
- All of these methods don't do anything and don't return anything ... they are just to allow you to achieve what functionality you need .

## CRUDServices General Hooks methods : 
- doBeforeOperationStart :  is called after validation operations is done and before to start a new database transaction (before start to create or update the model or its files or relationships ).
- doBeforeSuccessResponding : is called after the database transaction is commit ... it is called to perform a functionality before returning the success response .
- getSuccessResponseData : is called during returning the success response to return an array of data (wrapped in key = data ) with the response .
- doBeforeErrorResponding : is called after the database transaction is rollback ... it is called to perform a functionality before returning the error response .

## DataWriterCRUDServices (Storing & Updating Services) Hooks methods :
- doBeforeValidation : It is called before to start data validation operations .<br>
Ex : You may need to add a value to the current request data array before to start the validation operations  , You can do this like this.   


    protected function doBeforeValidation(): void;
    {
        request()->merge(["testKey" => "testValue"]);
    }

- doBeforeSavingCurrentModelProps : It is called to do affect on the model after filling its fillable data and before saving them ( newly created model , or the model wanted to be updated) .

Ex : You may want to edit some attributes for the main model before saving it in database , you can use this method and access the current model and its request data row like this :
    
    protected function doBeforeSavingCurrentModelProps( array $currentDataRow = []) : void
    {
        $this->Model->name = $currentDataRow["first_name"] . " " . $currentDataRow["last_name"];
    }

## DeletingServices Hooks methods :
- protected function checkDeletingAdditionalConditions()  : bool
  This method allow you to define additional conditions before start to delete anything related to model wanted to delete .<br>
Ex : You may want to avoid a purchase deletion if it is in the progress (after the admin changed its status from requested -> in progress ) so you will allow to delete the purchase only if it is in the first step 'requested'   , You can do this like this :


    protected function checkDeletingAdditionalConditions()  : bool
    {
      return $this->Model->status == "requested"; //Model is the purchase model wanted to delete by DeletingService 
    }

<hr>

## Using a custom ValidationManager :
- You may want to create your own custom ValidationManager to override its properties or methods , to do that :
  - create a new class and make it extend App\CustomLibs\CRUDServices\ValidationManagers\ValidationManager
  - Instruct the child CRUDService to use the new ValidationManager by overriding the method :
    protected function getValidationManager(): ValidationManager ;
    which it is defined in DataWriterCRUDService (override it to return a new custom ValidationManager instance) . 

        <?php
        
        class ClientStoringService extends SingleRowStoringService
        {      
            protected function getValidationManager(): ValidationManager 
            {
                 return ClientCustomValidationManager::Singleton();
           }
        }
