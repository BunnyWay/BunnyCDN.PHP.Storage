# BunnyCDN.PHP.Storage
The official PHP library used for interacting with the BunnyCDN Storage API.

### How to use:

The storage library is very simple to use. First, create the basic BunnyCDNStorage object with the authentication details. It's the basic object for interaction with the API.

```
$bunnyCDNStorage = new BunnyCDNStorage("storagezonename", "MyAccessKey", "sg");
```

The BunnyCDNStorage constructor takes the following parameters:
- **storageZoneName** - The name of your storage zone
- **apiAccessKey** - The API access key (password)
- **storageZoneRegion** - The storage zone region code (de, ny, or sg)


### Navigation:

- [Upload](#uploading-objects)
- [List](#listing-objects)
- [Download](#downloading-objects)
- [Delete](#deleting-objects)

<br/>

## Uploading objects:
To upload a file to the storage, you can use the uploadFile method. If the path to the object does not exist yet, it will be automatically created.

```
$bunnyCDNStorage->uploadFile("local/file/path/helloworld.txt", "/storagezonename/helloworld.txt");
```

<br/>

## Listing objects:
Get a list of of all objects on the given path.
```
$bunnyCDNStorage->getStorageObjects("/storagezonename/");
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


<br/>

## Downloading objects:
To download an object from the storage to a local file, you can use the downloadFile method.

```
$bunnyCDNStorage->downloadFile("/storagezonename/helloworld.txt", "local/file/path/helloworld.txt");
```

<br/>

## Deleting objects:
Deleting supports both files and directories. If the target object is a directory, the directory content will also be deleted.
```
$bunnyCDNStorage->deleteObject("/storagezonename/helloworld.txt");
```
