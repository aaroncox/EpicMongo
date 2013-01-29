EpicMongo
==========

MongoDb ORM for PHP (Inspired by [Shanty Mongo](https://github.com/coen-hyde/Shanty-Mongo), but rebuilt without Zend Framework)

This library is ***NOT*** complete yet, much of the functionality listed below is still in development.

Features: 
===

Easy Querying
---
Simple interface to quickly query a collection by it's short name, returning a Document or DocumentSet.

```php
<?php
$result = Epic_Mongo::db('shortname')->findOne();	// Returns Document
$results = Epic_Mongo::db('shortname')->find();	// Returns DocumentSet
?>
```

Create Document Types
---
Create different document types with specific requirements, functionality and can be extended.

```php
<?php
// A 'user' that someone would be logged in as
class LIB_Mongo_Document_User extends Epic_Mongo_Document {
	// The collection the documents are saved into
	protected static $_collectionName = 'users';
}

// A 'schema' is created for the connection to MongoDb
class LIB_Mongo_Schema extends Epic_Mongo_Schema {
	protected $_db = 'test_database';	// Defines which Database we use
	protected $_typeMap = array(
		'user' => 'LIB_Mongo_Document_User'	// This maps the 'shortname' of 'user' to the class 'LIB_Mongo_Document_User'
	);
}
?>
```

Easy Document Creation
---
Easily create a new document that is properly typed. 

```php
<?php 
// Create a Sample User 
$user = Epic_Mongo::doc('new:user');	// Adding 'new:' and then the 'shortname' from the schema
$user->id = 1;
$user->username = 'admin';
$user->password = 'password';
$user->save();
?>
```
Document Field Requirements
---
Create Requirements for specific fields on the Document Type. Listed below are examples of different options:

- '*doc:Class_Name*': (Optional) Forces the value of this field to be set to the specified Document Class when returned.
- '*ref:Class_Name*': (Optional) Forces the value of this field to be set to the specified Document Class when returned and automatically causes the conversion into a DBRef.
- '*req*': (Optional) Requires this field to be set in order to save.

```php
<?php
// A 'user' that someone would be logged in as
class LIB_Mongo_Document_User extends Epic_Mongo_Document {
	// The collection the documents are saved into
	protected static $_collectionName = 'users';
}

// A 'post' that a user could create
class LIB_Mongo_Document_Post extends Epic_Mongo_Document {
	// The collection the documents are saved into
	protected static $_collectionName = 'posts';
	// Any requirements on fields for this document
	protected $_requirements = array(
		// Author is a reference to a LIB_Mongo_Document_User and is required
		'author' => array('ref:LIB_Mongo_Document_User', 'req'),	
	);
}

// The schema must contain all of the different types
class LIB_Mongo_Schema extends Epic_Mongo_Schema {
	// Which database is this schema for?
	protected $_db = 'test_database';	
	// A map of all types this schema supports
	protected $_typeMap = array(
		'user' => 'LIB_Mongo_Document_User'	// This maps the 'shortname' of 'user' to the class 'LIB_Mongo_Document_User'
		'post' => 'LIB_Mongo_Document_Post'	// This maps the 'shortname' of 'post' to the class 'LIB_Mongo_Document_Post'
	);
}

// Create a User 
$user = Epic_Mongo::doc('new:user');	// The 'shortname' from the schema
// Some random example data
$user->id = 2;
$user->username = 'author';
$user->password = 'password';
// Save the User
$user->save();

// Create a Post document for the User
$post = Epic_Mongo::doc('new:post');

// Set the User as the author of the post, no need to create a reference
$post->author = $user;

// Set Extra 'post' information
$post->id = 1;
$post->title = 'Test Post';
$post->body = 'This is a test post, posted by User #1';
$post->created = time();

// Save the Post
$post->save();
?>
```

Reference Resolution
---
Automatically return the proper documents from DBRef references
```phtml
<?php
// This example uses the above example's classes and data
$post = Epic_Mongo::db('post')->findOne(array('id' => 1));
?> 
<!-- Renders the Post's Title -->
<h1><?= $post->title ?></h1>
<!-- Resolves the Reference for the Author, and Render's the User's Username -->
<h4><?= $post->author->username ?></h4>
<!-- Renders the Post's Body -->
<div><?= $post->body ?></div>
```

Returns Iteratable DocumentSets
---
When querying for more than one thing, automatically returns a DocumentSet

```phtml
<?php
// Get all posts sorted by the time field, descending
$posts = Epic_Mongo::db('post')->find(array(), array('time' => -1))
?>
<div>
	<!-- Iterate over the Posts -->
	<? foreach($posts as $post): ?>
	<div>
		<!-- Renders the Post's Title -->
		<h1><?= $post->title ?></h1>
		<!-- Resolves the Reference for the Author, and Render's the User's Username -->
		<!-- Iteration 1 = "admin", Iteration 2 = "author" -->
		<h4><?= $post->author->username ?></h4> 
		<!-- Renders the Post's Body -->
		<div><?= $post->body ?></div>
	</div>
	<? endforeach; ?>
</div>
```

Automatic Reference Querying
---
When you pass in a full object, it will convert it to a reference per the requirements on the document type.

```php
<?php
// Select User #1
$user = Epic_Mongo::db('user')->findOne(array('id' => 1));
// Build a Query for the posts collection where the author is a reference of the user
$query = array(
	'author' => $user,
);
// Find all posts
$posts = Epic_Mongo::db('post')->find($query);
?>
```

Export a DocumentSet to an Array
---
Incase the ArrayAccess and IteratorAggregate implementations don't do enough and you just want an array.

```php
<?php
// Find all our posts
$posts = Epic_Mongo::db('post')->find();
echo gettype($posts); // Returns "object" (specifically Epic_Mongo_DocumentSet)
echo gettype($posts->export()); // Returns "array" 
?>
```

Create a Document from an Array
---
Easily fill out a document by passing in an array of data.

```php
<?php
// Build some Sample data
$values = array(
	'username' => 'admin',
	'password' => 'password',
	'email' => 'email@email.com',
);
// Pass the Array into the ->setFromArray function
$user = Epic_Mongo::doc('new:user')->setFromArray($values)->save();
echo $user->username; // returns 'admin'
echo $user->password; // returns 'password'
echo $user->email; // returns 'email@email.com'
?>
```