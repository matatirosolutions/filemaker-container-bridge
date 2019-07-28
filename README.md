# FileMaker container bridge

This application will allow you to upload and download container data from your hosted FileMaker solution using the FileMaker Data API for FileMaker Server versions 17+.

It presumes that it will be able to connect to a single (configurable) layout in a FileMaker solution which contains the container you wish to push content into and / or retrieve it from. The script will require sufficient privileges to modify records in that table if you are uploading data. The account that you use can (and should) be limited to only that capability.

You can also have the script create new records in your `Files` table but at present it isn't possible to set any additional parameters with that request so this may nopt be very useful (i.e. you can't set other keys to associate the new file with other entities in your solution).

It was originally built to support integration of container data when using [FM BetterForms](https://fmbetterforms.com/). Thanks to [Recruiting Pro](http://recruitingprosoftware.com/) for allowing me to open source this code.

## Installation

1. Clone (or download and unzip) this repo to your web server (at a minimum it will need to support PHP 7.1.x) You could run this application under the FileMNaker web root of your server so long as you were using FileMaker server 18 or greater (which has the necessary version of PHP). For earlier FileMaker versions you'll need to either upgrade the version of PHP, or run this on another server.
2. `composer install` to install dependencies. If you don't have composer already installed on the machine you're installing to see [the composer intall page](https://getcomposer.org/download/)

For reference manually installing an application to a server as is described above, and configuring it as below, isn't necessarily the best way to go about things you'll probably want to use something like Ansible or Chef to help you with this, but these instructions are intended to help you 'get started'. Where you head from there is up to you :-) 
       
## Configuration

##### Configuring FileMaker
1. Create a specific layout (or even interface file) in your FileMaker solution to your underlying `Files` table. On that layout place the following fields
    1. `__pk_FileID` - the primary key in your table. This will be used in URIs so it should be a UUID (and not a serial number since that would enable a very simple enumeration attack)
    2. `File` - the container field which holds the files you're working with
    3. `Metadata` - this field is primarily used to work out the `Content-Type` header which is sent when someone is trying to access the content of the container. Most likely the field definition for that field will be, something like, but may not be limited to the below. You **must** call the key `fileName` (note capitalisation). You can add other metadata about the file to this JSON object if you wish, forexample if you're using this with BetterForms you may well be creating this object to pass through your data model to your frontend. This should be an auto-enter calc field.
        ```// FILE Info
        Let ([
            ~JSON = JSONSetElement ( "" ; 
                [ "fileName" ; GetContainerAttribute ( File ; "fileName" ) ; JSONString ] 
            ) 
          ]; 
        ~JSON
        )
2. Create a user account in your FileMaker solution which grants a user access to the above layout and fields with the `fmrest` extended privilege. This should be the **only** permissions that user has - exactly just enough, and no more.

##### Configuring the PHP
1. In the source folder you cloned onto your server, copy `.env` to `.env.local` and set configuration variables to connect to your FMS (the values which begin with `DATABASE_` from lines 30 onwards. The username and password should be those you created above.
2. Edit `src/Entity/File.php`
    1. set `@ORM\Table(name="File")` (line 16) to the name of the layout which this application will use, modifying `File` to match the name of the correspoding layout you created above.
    2. modify `* @ORM\Column(name="'__pk_FileID'", type="string", length=255)` (line 31) so that`name=""` matches the name of a UUID field on the layout you set previously. If your field name begins with an underscore (as per the example), then ensure that it is enclosed in single quotes `'` (as you would do in FileMaker when using `ExecuteSQL`), otherwise you don't need thses.
    3. modify `* @ORM\Column(name="File", type="string", length=255)` (line 39)) such that `name=""` is the name of the container field on the above layout. As with the UUID field, if that begins with an underscore, or is a related table, enclose that in single quotes, inside the double quotes.
    4. modify ` * @ORM\Column(name="Metadata", type="string", length=255` (line 46) to match a field on the layout you configured above which will provide metadata about the container field.
3. Edit `src/Entity/FileCreate.php`. This entity is used when a new file is being uploaded because we can't submit a `File` with the value of the container field set to null (FileMaker complains) which is what happens if we try to use the `File` entity to create the new record.
    1. set `@ORM\Table(name="File")` (line 16) to the name of the layout as above
    2. modify `* @ORM\Column(name="'__pk_FileID'", type="string", length=255)` (line 31) so that`name=""` matches the name of a UUID field as per above.
4. In the `public` folder, create a new folder called `uploads` (exact spelling and capitalisation). Ensure that your web user account has write access to this folder. If you're installing this in your FileMaker web root that user:group would be `fmserver:fmsadmin`. On Windows that should be `Users`.         
     
 ##### IIS only configuration
If you're installing this application on Windows (for example using the FileMaker server htdocs folder) you will need to add a rewrite rule to ensure that all traffic is routed through `public/index.php`. On OS X this is taken care of by the `.htaccess` file in the public folder.   
        
## Usage ##        

1. If you're wanting to download container content then you need to construct a URI of the format
    `https://server/api/file/**uuid**/download`
2. If you're wanting to upload container data to a specific record, then you should `POST` to `https://server/api/file/**uuid**/upload` with the file being uploaded in a post variable called `file`.  If you were using Dropzone in BetterForms your configuration would look something like this.
   ```
   {
       "label": "Upload File",
       "model": "file",
       "options": {
           "dictDefaultMessage": "Click or Drop files here to upload.",
           "paramName": "file",
           "url_calc": "'https://server/api/file/' + model.fileID +'/upload"
       },
       "required": true,
       "styleClasses": "col-xs-12 col-sm-8",
       "type": "dropzone"
   }   
3. If you want to create a new file record when uploading `POST` to `https://server/api/file/upload/new` again with the file in the post variable `file`.

## Contact Details
Steve Winter
Matatiro Solutions
steve@msdev.co.uk
