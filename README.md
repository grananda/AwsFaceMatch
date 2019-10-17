
  
# AWS Laravel Face Match  (BETA)  
This component integrates face recognition to conventional Laravel models such as users with avatar images. This package compares a single face image against an indexed collection of images and returns a matching model with a precision greater than 80%.  
  
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
  ## Setup your Eloquent models  
Laravel Face Match can recognize people from different models. For example, if you have models for clients and employees, you can request an image match against any of those models.

To start using this package, you must have the model(s) registered in the facematch configuration file as following:

``` php
'recognize' => [  
    Employee::class => [  
        'collection' => 'entity',  
        'identifier' => 'uuid,',  
        'media_file' => 'media_url',  
    ],
],
```

 Where every element of the `recognize` array element correspond to an existing class. Each element key must match the model class name. Additionally, the elements must be completed as followed per each of the added models:
 

 - **Collection**:  wherein AWS will the avatar images and user references be indexed. If none, a combination of the model `namespace` and `className` will be taken as the default collection name.
 - **Media Field**: determines which field in the model database will the avatar image URL be stored.
 - **Identifier**: which unique field in the model database will be used to identify the record once a face match occurs. It is recommended to use a **UUID** field for such a purpose.  
    
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
        'media_url',
    ];   
} 
```    
In addition, both the `mediaField` and `identifier` fields should be included in the model `fillable` array as well as in your database migrations if necessary.  
  
You can add the face match functionality to as many models as you wish as far as they do not share the same collection.  
  
No AWS S3 Bucket it needed but could be used for storing your model images. All that is needed is an image URL.  
  
The system only accepts **single face images** when indexing an entity for future recognition.  
   
 ## How to Use it  
When a model using the `FacialRecognition` trait creates a new object, the avatar image is stored in the AWS Rekognition services along with the record identifier. The same occurs when the record is updated with a **different** image URL. No Rekognition index action will tale place if the record lacks media URL when saving the item.  
  
### Identifying models from an image  
If we wish to find a model in our system that may match a specif image, we can use the following command:  
  
```php 
Employee::faceMatch('path/or/url/to/image.png');  
```  
Where `Employee` can be replaced by any other model using the face match feature. If there is a match, the command will return the model object corresponding to the given image or `false` otherwise.  
  
### Cleaning up  
The folowing command will remove all images from a model collection. Please use it wisely.  
```php 
Employee::purgeCollection();  
```
### Mass Indexing  
To indexing already existing data, run the following command from the console:
```sh 
php artisan facematch:index
```
  
## Comming Soon  
- Allow for specific indexed models to be removed from a collection.  
- Manually specify the accuracy threshold when detecting matches.  
  
Please feel free to comment and make requests.
