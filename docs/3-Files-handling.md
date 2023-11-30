### Overview :
- CRUDServices handles all file operations  (uploading & deleting & file full path retrieving ) itself without any need to write an extra code , You should only to provide the service core some info about files you want it to handle , and that will be by implementing some interfaces on the model who has files .
- Files Will Be Uploaded To Model's Storage Folder After Setting The File's Name To Model Database's table (and nothing will be moved if the database transaction failed).
- Files needed to delete will be deleted in the background by cron job by
App\CustomLibs\CRUDServices\FilesOperationsHandlers\OldFilesDeletingHandler\OldFilesDeletingHandler .
- For 

### Preparing Model :

#### Implementing App\CustomLibs\CRUDServices\Interfaces\MustUploadModelFiles :
- Any model has files and wanted to handle them by CRUDServices must implement this method found in this interface :
  public function getModelFileInfoArray() : array
- This method must return an array of subArrays , which each subArray is a file info array and can contain these keys :
  - RequestKeyName : Is the important part of this array .... it is the key used to send the file from the frontend .  
  - ModelPathPropName : Is the model's column name which will contain the file name ( set it only if  RequestKeyName != the column name in database ) .
  - multipleUploading : Its default value = false , set it true if you want to handle multiple files (( for the same model column )) ,
  if you want to handle many files in the same request and for the same model (( but for the different columns )) return many file info subArrays from the method (( and <b>DO NOT</b> without set this key to true )).  


Ex :

       public function getModelFileInfoArray() : string
       {
         return [ [ "RequestKeyName" => "picture" ] ];
       }

#### Implementing App\Interfaces\HasStorageFolder :
- Any model has files and wanted to handle them by CRUDServices must implement this method found in this interface :
public function getDocumentsStorageFolderName() : string;
- This method must return a string that present the folder name will hold the current model's all files .
- important note : in the naming of this folder (DO NOT use) the primary key if you are using StoringService to store the model newly ... because the file info will be processed before the model is created ... so it will not have a primary key .
- You can instead implement App\Interfaces\HasUUID on the model (inherited from App\Models\BaseModel class) and will generate a UUID named hashed_id for the model , 
use it in database & fillable ,  getDocumentsStorageFolderName naming method .

Ex :

       public function getDocumentsStorageFolderName() : string
       {
         return "Pictures/Users/" . $this->hashed_id;
       }

### File Model ( required and not null file  ) VS. Model Has File ( optional and nullable file) :
- This part is not related directly to CRUDServices , but it is important to help you to determine how you should plan your models files.
- Model's File Case : When you have a model that has a file as a column or a property with another properties that is mean you have 2 Cases for its files :
  - optional file : can be deleted by send it request value as null or without uploading it from the start ,
  because it is nullable in database (Don't forget to make its field nullable in validation rules ).
  - required file : it is required because you already have determined that when you planed the business rules of your application , Don't request for deleting it and make sure its request value always is a file object or path string by validation custom rules :
    App\CustomLibs\CRUDServices\ValidationManagers\CRUDValidationRules\FileValidationRules\SingleFileOrSinglePathString , App\CustomLibs\CRUDServices\ValidationManagers\CRUDValidationRules\FileValidationRules\MultiFileOrMultiPathString .
  - Ex : Employee passport photo is a required ... while its avatar is nullable can be uploaded or no matter .
  - Ex For Using Custom Validation Rules:
  
        public function rules(array $data)
        {
          return [ "name" => ["required" , (new App\CustomLibs\CRUDServices\ValidationManagers\CRUDValidationRules\FileValidationRules\SingleFileOrSinglePathString() ) ] ; ]
        }

- File Model Case : This model should be a model to contain and present a file info ... so any request to delete the file or changing it must affect on the model (deleting or updating affect) .
  Ex : If you have a ClientAttachment model ... it will be contain props like (path , extension , folderName , client_id , size ) ... 
 if you update the related parent (client) and wanted to delete its file you must request to delete the whole ClientAttachment model with its file from the storage ,
and in the other hand if you wanted to update the file you must update the whole ClientAttachment model and change its file in  the storage.
  (For more details look for Relationship Handling docs part ... which contain more details about updating and deleting a relationship model with its files ).

### Important Notes For Frontend ( File Handling Behaviors Depends On Request Values Comes From Frontend ) :
- To upload a file : send a file object with the file request 's key , and its value is preferred to be the column name used in model's database table.
- To delete a file : send null value with the file request 's key .
- To keep file without changing (( You Must send its path string with the file request's key , Don't send null if you don't want to delete it ))

#### More Details : 
- Any File Uploading Operation Will Not be achieved <b>If File Request Key's Value !== File Object</b>
  (That means : When You Send The Value AS File Path's String It Means <b>(You Don't Want To Change The OLD File)</b>) .
- You Can Upload Any File Extension You Want <b>If The Validation rules allow to do that ( Specify Specific Extensions you want in validation rules )</b>

#### File Full Path Retrieving Handling using toArray method :
- if the model :
  - is child of App\Models\BaseModel .
  - implements App\CustomLibs\CRUDServices\Interfaces\MustUploadModelFiles .
  - implements App\Interfaces\HasStorageFolder .
  You don't need to do anything ... use toArray method where you want and the file properties values found in the returned array will be a full path of the file . 
  Note : toArray method doesn't change the files properties values found on the model itself ... it just returns an updated array contains the required path values .
