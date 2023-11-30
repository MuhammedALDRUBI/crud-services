# Relationships Handling

## Overview
- Here you can handle the relationships of the model used in CRUDService child class .
- Relationship creating , updating , deleting will be handled based on request data values ( look to relationship operations part in this docs page) .

## usage steps :  
### 1-  Preparing the model
The model used in CRUDServices and wants it to handle its relationships (( MUST )) be child of App\Models\BaseModel .

### 2- Owned Relationships Handling : HasOne (one to one) , HasMany (one to manu) Relationships 
- The relationship owner model must implement this interface (App\CustomLibs\CRUDServices\Interfaces\OwnsRelationships).
- By implementing this interface the model will be required to define the method :
  public function getOwnedRelationships() : array;
  This method must return an array of App\CustomLibs\CRUDServices\CRUDComponents\CRUDRelationshipComponents\OwnedRelationshipComponent objects .

#### Using OwnedRelationshipComponent class :
- use create(string $relationshipName , string $foreignKeyName) static method to get an instance of the component .
- for a relationship's updating operation :
  - use setUpdatingConditionColumns public method to set the columns must be checked in each data row in the relationship request data .
    - These columns will determine if the exists relationship row will be updated or deleted , and doesn't affect on the new rows .
    - set its value as an array contains the most important keys in the relationship database table .
        Ex : ["id" , "client_id"] ... here we want to update the relationship row if its id and client_id values is correct and already exist in the client_attachments database table .
    - <b>When Must I use setUpdatingConditionColumns : </b> <br>
        1- You Must set this array value if your relationship model 's primary key is not "id" . <br> 
        2- set it when you need to use multiple column as an updating condition columns . <br>    
        
- Ex : We want to define an owned relationship in getOwnedRelationships method's returned array , info relationship's primary key = fire_system_id , And it is the foreign key in the same time . 

      OwnedRelationshipComponent::create("info" , "fire_system_id")->setUpdatingConditionColumns(["fire_system_id"]);
- For a relationship deleting operation :
  - Each owned relationship is deleted automatically when its parent is deleted and there is a cascading on Deleting on the foreign key , you can stop this by (not logical to stop that but for more options ) :
    - (look for "preparing database relationships" docs part in DeletingServices-usage docs ) . 
    - disabling parentDeletingCascading in the OwnedRelationshipComponent by disableParentDeletingCascading public method.
    - make the parent model is related to the relationship model (not owner) by removing cascadeOnDelete method on the definition of the foreign key in the relationship's migration file (or using nullOnDelete method instead).
    - <b>Important note </b> : By disabling parentDeletingCascading in the OwnedRelationshipComponent without changing foreign key deletion constraint method (without removing cascadeOnDelete method)
    you only avoid to delete the relationship model files (and its sub relationship models files) ... but the relationship row in the database will be affected by deletion operation ( So you will get a wrong result ) .  

### 2- Participating Relationships Handling : BelongsToMany (many to many) Relationships 
- The relationship owner model must implement this interface (App\CustomLibs\CRUDServices\Interfaces\ParticipatesToRelationships).
- By implementing this interface the model will be required to define the method :
  public function getParticipatingRelationships() : array;
  This method must return an array of App\CustomLibs\CRUDServices\CRUDComponents\CRUDRelationshipComponents\ParticipatingRelationshipComponent objects .

#### Using ParticipatingRelationshipComponent class :
- use create(string $relationshipName , string $foreignKeyName) static method to get an instance of the component .
- for a relationship's pivot columns (the columns found on the middle table ) :
    - use setPivotColumns public method to set the columns are allowed to add to the middle table by many-to-many relationship.
    - To set these columns they must be defined in the definition of the belongsToMany relationship definition by withPivot method .
    - <b>When Must I use setPivotColumns : </b> <br>
      If you have columns must be inserted to the middle table ... Because the columns are not set in this array ( WIL NOT be inserted for more protecting ) . <br>
- Ex : If we have comments relationship between post and logged user , the many-tp-many relationship definition in the User's model will be like : 
  
      public comments() : BelongToMany
      {
        return $this->belongsToMany(Post::class , "user_id" , "post_id" , "id" , "id")->withPivot('comment_content', 'created_by');
      }
  
And for getParticipatingRelationships method returned relationship component objects :

    ParticipatingRelationshipComponent::create("comments" , "post_id" )->setPivotColumns(['comment_content', 'created_at']);

### 3- Preparing request data :
- CRUDServices expects to receive an Array Of subArrays where :
  - The parent array will be sent in key = relationshipName defined in RelationshipComponent object (OwnedRelationshipComponent , ParticipatingRelationshipComponent ) .
  - Each subArray present a relationship row data .

  - Ex :
  
         $requestData = [
                           "attachments" => [
                                                ["path" => "attachment1_path" , "attachment_type" => "attachment1Type"] ,
                                                ["path" => "attachment2_path" , "attachment_type" => "attachment2Type"] ,
                                                ["path" => "attachment3_path" , "attachment_type" => "attachment2Type"]
                                            ]
                        ]
 
### 4- Preparing Request Form ( Data Validation ) :
- Relationship values will be validated in the same request ... So put its validation rules in the same Request Form class used to validate the main model's values .
- There is tow type of validation rules the CRUDServices expects to receive :
  - General validation rules (using rules method).
  - Keys advanced validation rules (using getRelationshipsKeyAdvancedValidationRules method ).

- rules method :
  - It is the same rules method used to return the main model validation rules .
  - relationship validation rule's key must be like this convention 
    - "relationshipName.*.column" => [rules ] 
    - Ex : "attachments.*.type" => ["required" , "string" ]
  
  - Add the validation rules of the relationship those are not related to checking data in database or cache or any data resource ... just add the rules check the data form and data type .
- If there is need to define rules related to files use the custom rules :
  App\Rules\FileValidationRules\SingleFileOrSinglePathString , App\Rules\FileValidationRules\MultiFileOrMultiPathString ( look at files handling docs part ).
  Ex : We want to validate the name of a client , its attachment relationship 's type column , and want to validate comments relationship's expense_request_id ... so in the same rules method of the same Request Form class we will write : 

      public function rules(array $data)
      {
          return [
                    "name" => ["required" , "string" ],
                     "attachments.*.type" => ["required" , "string" ], 
                     "comments.*.expense_request_id" => ["required" , "numeric" ], 
                 ]
      }

- getRelationshipsKeyAdvancedValidationRules method : If there is any need to check column's value in a data resource like cache or database follow these steps :
    - make the Request class implement App\CustomLibs\CRUDServices\Interfaces\ValidationManagerInterfaces\NeedsRelationshipsKeyAdvancedValidation interface ,
     And implement the method 
    public function getRelationshipsKeyAdvancedValidationRules(array $data = []) : array;
  - getRelationshipsKeyAdvancedValidationRules method must return an associative array where :
    - Each key is a relationship name , and its value is a validation rules array those can be applied in a data resource like database .
        - use $data array in rules and getRelationshipsKeyAdvancedValidationRules methods to use request data values during defining the rules .
        - Ex : attachments is an owned relationship between Client and ClientAttachment models , comments is a participating ( manyToMany ) relationship between current logged User model and Expense Request Model by adding a comment

                    public function getRelationshipsKeyAdvancedValidationRules(array $data = []) : array
                    {
                        return [
                                "attachments" => [ 
                                                   attacment_number" => ["required" , Rule::uniqeu(client_attachments,attacment_number)->ignore($data["id"]) ]
                                                   // id here is the primary key of the ClientAttachment model ... 
                                                   //It is used to ignore the current row when checking if the  value is unique in the table (you need to use this for updating request form)
                                                 ],
                                "comments" => [ 
                                                  "expense_request_id" => [  "exists:expens_requests,id"]
                                                 //Here you can validate rows of a manyToMany Relationship and check its values in DataResource like Database
                                              ]
                               ];
                    }

- <b> Note For (( Updating  Owned )) Relationships :</b> While using getRelationshipsKeyAdvancedValidationRules method , If the relationship model's primary key is not exists in request data array ,  It will be added to use it during defining the rules related to the relationship model's base data .
- <b> Note For Participating Relationships :</b>    While using getRelationshipsKeyAdvancedValidationRules method , You can only check for 'exists' rule ... 'unique' rule is only work for the first data row then it will get unexpected result (wrong result).  

<hr>

## Relationships Operations :

### Relationship Row Creating :
- To create a new relationship row :
    - Send the relationship fillable values (or values will be inserted) .
    - Don't Send Any Updating condition Column Or Their Values in request data array.

### Relationship Row Updating :
- To Update a found relationship's row :
    - Send the relationship fillable values (or values will be inserted) .
    - You Must Send The Updating Condition's values you specify when you specified in RelationshipComponent object (OwnedRelationshipComponent , ParticipatingRelationshipComponent ) .

### Relationship Row Deleting :
- To Delete a found relationship's row :
    - Don't Send Its Data Row and Will Be Deleted If It Is Exists In Database .

<hr>
 
#### Relationship's Files Handling :
- Any model has files its files will be processed at the same way we talk in Files Handling docs part ( For participating relationships no files will be handled if there is no model to present the middle table).
- The relationship model can have an optional file (one column presents the file , And it is nullable like employee's avatar) ,
  or it can be a file model (presents the info of a file ) ... we explained the differences in Files Handling docs part .
- For deleting an optional file or changing it is enough to change its request key's value , 
But for a file model it must affect on the whole model info for updating operation and deleting the model for deleting operation ,
And for deleting a relationship file model (( DO NOT )) use the Deleting CRUDServices .... Delete it as a relationship model deleting as we talked above. 
