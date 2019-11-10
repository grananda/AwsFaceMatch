
  
# AWS Laravel Face Match  (BETA)  
This component integrates face recognition to conventional Laravel models such as users with avatar images. This package compares a single face image against an indexed collection of images and returns a matching model with an accuracy score greater than 80%.  
  
This component uses the  [AWS Rekogniton](https://aws.amazon.com/es/rekognition/) engine. Please review the site for credentials setup and prices.

## Installation    
 To install through composer, simply execute the following command :    
```sh 
composer require grananda/laravel-face-match  
```  
Execute the following command to copy the configuration file to your application config folder:  
  
```sh  
php artisan vendor:publish --provider="Grananda\AwsFaceMatch\FaceMatchServiceProvider"  
```  
  
Add your AWS `AWS_ACCESS_KEY_ID` and `AWS_SECRET_ACCESS_KEY` credentials to your `.env` file.  
  
For the component to work, you need a set of AWS key and secret as well as the zone where you wish to operate. This values will be read from you `.env` file as usual. Other configuration parameters will be included in the `facematch.php` config file.
  
Please remember that for older versions on Laravel <5.5, it is necessary to register the component service provider in your `config/app.php` file under the `$providers` array element.  
  
```php  
Grananda\AwsFaceMatch\FaceMatchServiceProvider::class,  
```  
  
Also, in the `$aliases` array element, add the following available facades for this package.  This is only necessary if you wish to access the package services method outside the trait scope.
  
```php  
'FaceMatch' => Grananda\AwsFaceMatch\Facades\FaceMatch::class,
'FaceCollection' => Grananda\AwsFaceMatch\Facades\FaceCollection::class,  
```  
Finally, run thepackage migrations that will create tables where indexed information will be stored.
```php  
php artisan migrate 
``` 

  ## Setup your Eloquent models  
Laravel Face Match can recognize people from different models. For example, if you have models for clients and employees, you can request an image match against any of those models.

To start using this package, you must have the model(s) registered in the face match configuration file as displayed below:

``` php
'recognize' => [  
    Employee::class => [  
        'collection' => 'entity',  
        'identifier' => 'uuid,',  
        'media' => [
	        'field'  => 'avatar_image',
	        'binary' => false,
	    ],
    ],
],
```

 Where every element of the `recognize` array element correspond to an existing model class. Each element key must match the model class name. Additionally, the elements must be completed as followed per each of the used models:
 

 - **Collection**:  wherein AWS will the avatar images and user references be indexed. If none, a combination of the model `namespace` and `className` will be used as the default collection name.
 - **Identifier**: which unique field in the model database will be used to identify the record once a face match occurs. It is recommended to use a **UUID** field for such a purpose.  
 - **Field**: determines which field in the model database will the face image URL be stored.
 - **Binary**: defines if the field that stores the image information is a binary or blob field. Default is `false`.
    
To make a model suitable for face recognition, you must add the `FacialRecognition` trait to your models as illustrated below.    
    
```php 
namespace App\Models;    

use Illuminate\Database\Eloquent\Model; 
use Grananda\AwsFaceMatch\Traits\FacialRecognition;    
 
 class Employee extends Model {  
 
     use FacialRecognition;  
 
     protected $fillable = [
        'name',
        'uuid',
        'avatar_image',
    ];   
} 
```    
In addition, both the `field` and `identifier` fields should be included in the model `fillable` array as well as in your database migrations if necessary.  
  
You can add the face match functionality to as many models as you wish as far as they do not share the same collection.  
  
No AWS S3 Bucket it needed but could be used for storing your model images. All that is needed is an image URL or a binary database record with the image data.  
  
The system only accepts **single face images** when indexing an entity for future recognition.  
   
 ## How to Use it  
When a model using the `FacialRecognition` trait creates a new object, the avatar image is stored in the AWS Rekognition services along with the record identifier. The same occurs when the record is updated with a **different** image URL. No Rekognition index action will take place if the record lacks a media URL or data when saving the item.  

Wiselike, all stored indexed information is removed from the AWS when a model is locally removed.
  
### Identifying models from an image  
Use the following command If you wish to find a model that may match a specif image:
  
```php 
Employee::faceMatch('path/or/url/to/image.png');  
```  
Where `Employee` can be replaced by any other model using the face match feature. If there is a match, the command will return the model object corresponding to the given image or `false` otherwise.  

Although the system can index images from binary database fields, the match or recognition process must use a file system based stored image path to function. Pure binary comparison is not yet supported. Please help us improve this component by providing a valid use case for this scenario.
  
### Forgetting an Image
Use the following comand to remove a local record from the remote AWS Rekognition system. This feature can be useful when a user requests to be removed from the face recognition system.
```php 
Employee::faceForget($object);  
```

### Cleaning up  
The following command will remove all images from a model collection. Please use it wisely.  
```php 
Employee::purgeCollection();  
```
### Mass Indexing  
To indexing already existing data, run the following command from the console:

```sh 
php artisan facematch:index
```
  
## Comming Soon  
- Manually specify the accuracy threshold when detecting matches.  
  
Please feel free to comment and make requests.
