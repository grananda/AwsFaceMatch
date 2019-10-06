
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
For the component to work, you need a set of AWS key and secret as well as the zone where you wish to operate. This values will be read from you `.env` file as usual. Other configuration parameters will be included in the `facematch.php` config file as the component evolves in future versions.

Please remember that for older versions on Laravel <5.5, it is necessary to register the component service provider in your `config/app.php` file under the `$providers` array.

```php
Grananda\AwsFaceMatch\FaceMatchServiceProvider::class,
```

Also, in the `$aliases` array add the following facade for this package.

```php
'FaceMatch' => Grananda\AwsFaceMatch\Facades\FaceMatch::class,
```
  
## Setup your Eloquent models
Laravel Face Match can recognize people from different models. For example, if you have a model for clients and another one for employees, you can request an image match against any of those models independently.  
  
To make a model suitable for face recognition, you must add the following elements to your models as illustrated below.  
  
```php  
namespace App\Models;  
  
use Illuminate\Database\Eloquent\Model;  
use Grananda\AwsFaceMatch\Traits\FacialRecognition;  
  
class Employee extends Model  
{
  use FacialRecognition;

  protected $fillable = [
    'name',
	'uuid',
	'media_url',
];  

  public function recognizable() { 
    return [
      'collection' => 'emloyees',
      'mediaField' => 'media_url', 
      'identifier' => 'uuid', 
    ]; 
  }
}  
```  
  
There are a few important things to add to your models:  

 - Use of the `FacialRecognition` trait. 
 - Add the `recognizable` method. This method will define  the following parameters:
	 - **Collection**:  wherein AWS will the avatar images and user reference be indexed. If none, a combination of the model `namespace` and `className` will be taken as the default collection.
	 - **Media Field**: determines which field in the model database will the avatar image URL be stored.
	 - **Identifier**: which unique field in the model database will be used to identify the record once a face match occurs. It is recommended to use a **UUID** field for such a purpose.

It is important to mention that both the `mediaField` and `identifier` fields should be included in the model `fillable` array as well as in your database migrations if necessary.

You can add the face match functionality to as many models as you want as far as they do not share the same collection.

No AWS S3 Bucket it needed but could be used for storing your model images. All is needed is an image URL.

The system only accepts **single face images** when indexing an entity for future recognition.
 
 ## How to use it
When a model using the `FacialRecognition` trait creates a new object, the avatar image is stored in the AWS Rekognition services along with the record identifier. The same occurs when the record is updated with a **different** image URL. No Rekognition index action will tale place if the record lacks media URL when saving the item.

### Identifying Models from Image
If we wish to find a model in our system that may match a specif image, we can use the following command:

```php 
Employee::faceMatch('path/or/url/to/image.png');
```
Where `Employee` can be replaced by any other model using the face match feature. If there is a match, the command will return the model object corresponding to the given image or `false` otherwise.
