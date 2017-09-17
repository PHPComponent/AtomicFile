# AtomicFile: Atomic operations #

AtomicFile is package for atomic operations with file. It guarantees nobody will overwrite your file, while you are writing into/reading file. 

```php
$file_path = dirname(__FILE__).'/../tmp/test.txt';
$reader = new PHPComponent\AtomicFile\AtomicFileReader($file_path); //create instance of Reader
print_r($reader->readFile()); //read file
```

## Important notice ##
It works only when other writers/readers use same class, function flock() cannot lock file for others functions then flock().
