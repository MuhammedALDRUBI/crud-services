# Overview :
DeletingService handles the deletion operation of an exist model and its files , relationships .

## How it works :
- It only requests to delete the constructor-passed model , <b>And keep the RDBMS (Relational Database management system) decide if the record can be deleted or not <b>.
- If the record can't be deleted the DeletingService will handle the exception and cancel the process before deleting any files or relationships . 
- It works with database transaction to make this operation atomic .
- Only owned relationships will be deleted With the model .
- All files need to delete will be prepared to delete with cron job after the database deletion operation is executed successfully (Model and its owned relationship files ).

<hr>


## When the database record can be deleted :
-  When model is not used in the system ( If there is no referring to the record from another database's record ) .
<br>OR<br>
- If the model is used in the system <b>But as optional using </b> (When it is related optionally (by nullable column ) to another database's record ).
<br>OR<br>
- If the model is just defined in the system <b> And owns relationship records with cascading on deleting </b>. 

## How can I make constraints on the deletion operation executed by this service :
- By good using of the foreign key constraint in the relational database management systems . 
- By using hooks to determine additional conditions to be checked before starting to delete anything .

<hr>

## Service usage

#### Step 1 : Creating a new Deleting service :
- create a new class and make it extend the main DeletingService class :
App\CustomLibs\CRUDServices\CRUDServiceTypes\DeletingServices\DeletingService . 

#### Step 2 : Defining the required methods in the newly created child UpdatingService class :
- protected function getModelDeletingSuccessMessage() : string;
- protected function AuthorizeByPolicy(): bool

#### Step 3 : create an instance of the newly created service and use the public method 'delete' .
- It requests you to pass the model will be deleted into the constructor of the service .

Follow up the steps bellow to prepare the module to be able to use by DeletingService .

<hr>

#### preparing database relationships to allow te parent model to be deleted :
<b> Any record can be deleted if it is not related to another records in database , So in this paragraph we will discuss how to make constraint on deletion operation will be executed on the model's record which is related to some records in database .</b>

- To make a record deletable even it is related to another records in database :
    - use cascadeOnDelete method when you define the belongsTo relationship between the parent record and its owned relationships . <br>
      Ex : If a client record related to its attachments and addresses ... they are deletable when the client is deleted because they are present its own info .

          $table->foreignId("client_id")->constrained("clients")->cascadeOnDelete() ; // Refering from Client_attachments or client_addresses tables 's client_id column to clients table 's id column

    - use nullOnDelete method when you define the belongsTo relationship between the parent record and the optionally related records ( the foreign key must be nullable column to be set as null) . < br>
      Ex : The same client above has an address which has an optional area info ... so it will be related to an area record optionally (When the related area record is deleted there is no data missing and null can be set ) .

          $table->foreignId("area_id")->nullable()->constrained("areas")->nullOnDelete(); // Refering from client_adddress table to areas table's id column

- To make a record undeletable :
    - Don't use any deleting constraint method when you define the belongsTo relationship between the record and the other related records ( required and can't be deleted when it is related to another records ) .<br>
      Ex : If the same client is used in the system and participates in another relationships ( If the client bought some things before for example , so maybe its record related to invoice records or related to purchase records  , so it undeletable) .

          $table->foreignId("client_id")->constrained("clients"); //  Refering from Client_purchase or client_invoices tables 's client_id column to clients table 's id column


#### Preparing the model :
- Make sure to add table name to <b>table property </b>.
- If the model has any relationship or files look at their docs part to handle them ( If the model has files or relationships it will be handled automatically ) .

#### using the policies to authorize the deleting action :
- As you know ... Nobody should be able to delete something in the system if he hasn't the permissions those allow him to perform this deleting action .
- AuthorizeByPolicy method : it must return true or false .... define the condition must be true to allow the user to perform this deleting action (You should use a policy calling here).
- App\Policies\BasePolicy::check method : to call a policy action for the logged user .
  Ex :

      protected function AuthorizeByPolicy(): bool
      {
        return BasePolicy::check('delete', ControlPanel::class);
      }

<hr>

### More functionality :
- If you need to change the deleting failing error message , You can override This method .
protected function getModelDeletingFailingErrorMessage() : string
- Look at hooks part in adding-additional-functionality docs page to use perform more functionality at some point of the current request life cycle.
