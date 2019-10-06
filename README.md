
# AWS Laravel Face Match  (BETA)
This component integrates face recognition to conventional laravel models such as users with avatar images. This package will return a current model record from an image where the user appears with a precision greater than 80%.

This component uses [AWS Rekogniton](https://aws.amazon.com/es/rekognition/) engine. Please review the site for credentials setup and prices.
  
## Installation  
  
To install through composer, simply execute the following command :

```sh  
composer require grananda/laravel-face-match
```
Execite the following command to copy the configuration file to your appliction config folder:

```sh
php artisan vendor:publish --provider="Grananda\AwsFaceMatch\FaceMatchServiceProvider"
```
In order for the component to work all your need are a set of AWS key and secret as well as the zone where you wish to operate. This values will be read from you `.env` file as usual. Other configuration parameters will be included in this `facematch.php` config file as the component evolves in future versions.

Please remember that for older versions on Laravel <5.5, it is necessary to register the component service provider in your `config/app.php` file under the `$providers` array.

```php
Grananda\AwsFaceMatch\FaceMatchServiceProvider::class,
```

Also, in the `$aliases` array add the following facade for this package.

```php
'FaceMatch' => Grananda\AwsFaceMatch\Facades\FaceMatch::class,
```
  
## Setup your Eloquent models
  
Laravel Face Match can recognize people from different models. For example, if you have a model for clients and a second one for employees, you can request request an image match over any of those models independently.  
  
In order to make a model suitable for face recognition, you must add the following elements to your models as illustrated below.  
  
```php  
namespace App\Models;  
  
use Illuminate\Database\Eloquent\Model;  
use Grananda\AwsFaceMatch\Traits\FacialRecognition;  
  
class Employees extends Model  
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
  
There are a few major things to add to the models:  

 - Use the `FacialRecognition` trait. 
 - Add the `recognizable` method. This method will define  the following parameters:
	 - **Collection**:  where in AWS will the avatar images and user reference be stored. If none is specific, the model `namespace` and `className` will ba taken as a reference. This is an optional value.
	 - **Media Field**: determines wich field in the model database will the avatar image url be stored.
	 - **Identifier**: which unique field in the model database will be use to identify the record once a face match occur. It is recommended to use a **UUID** field for such purpose.

It is important to mention that both `mediaField` and `identifier` fields should be included in the model `fillable` array.

You can add the face match functonality to as many models as you may wish as far as the do not share the same collection.

No AWS S3 Bucket it needed but could be use for storing your mdoel images.
 
 ## How to use it
When a model using the `FacialRecognition` trait creates a new object, the avatar image is stored in the AWS Rekognition services along with the record identifier. The same occurs when the record is updated with a differente image url. No Rekognition index action will happen in the record lacks of media url when saving the item.

### Identifying Models from Image
If we wish who find a model in our system that may match a specif image we can use the following command:

```php 
Employee::faceMatch('path/or/url/to/image.png');
```

If there is a macth, the command will return the model object corresponding to the given image or `false` otherwise.
