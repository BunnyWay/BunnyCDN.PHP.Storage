# Bunny Storage PHP library

The official PHP library used for interacting with the BunnyCDN Storage API.

## Install

```
composer require bunnycdn/storage
```

## Usage

Create an instance of the `\Bunny\Storage\Client` with the authentication details

```php
$client = new \Bunny\Storage\Client('access-key', 'storage-zone', \Bunny\Storage\Region::SINGAPORE);
```

The BunnyCDNStorage constructor takes the following parameters:
- **apiAccessKey** - The API access key (password)
- **storageZoneName** - The name of your storage zone
- **storageZoneRegion** - The storage zone region [code](src/Region.php#L9-L17) (de, ny, or sg)

### Navigation:

- [Upload](#uploading-objects)
- [Download](#downloading-objects)
- [List](#listing-objects)
- [Delete](#deleting-objects)
- [putContents](#put-file-contents)
- [getContents](#get-file-contents)

---

### Uploading objects

```php
$client->upload('/path/to/local/file.txt', 'remote/path/hello-world.txt');
```

---

### Downloading objects

```php
$client->download('remote/path/hello-world.txt', '/path/to/local/file.txt');
```

---

### Listing objects

```php
$items = $client->listFiles('remote/path/');
```

The StorageObject contains the following properties:
- **Guid** - The unique GUID of the file
- **UserId** - The ID of the BunnyCDN user that holds the file
- **DateCreated** - The date when the file was created
- **LastChanged** - The date when the file was last modified
- **StorageZoneName** - The name of the storage zone to which the file is linked
- **Path** - The path to the object
- **ObjectName** - The name of the object
- **Length** - The total of the object in bytes
- **IsDirectory** - True if the object is a directory, otherwise false.
- **ServerId** - The ID of the storage server that the file resides on
- **StorageZoneId** - The ID of the storage zone that the object is linked to
- **FullPath** - The full path to the file

---

### Deleting objects

```php
$client->delete('remote/path/hello-world.txt');
```

---

### Delete multiple objects

```php
$errors = $client->deleteMultiple(['file1.txt', 'file2.txt', 'non-existing.txt']);
var_dump($errors);

/*
array(1) {
  'non-existing.txt' =>
  string(16) "Object not found"
}
*/
```

---

### Put file contents

```php
$content = 'Hello, world!';
$client->putContents('hello-world.txt', $content);
```

---

### Get file contents

```php
$content = $client->getContents('hello-world.txt');
echo $content; // Hello, world!
```
