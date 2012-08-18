Epic-Mongo
==========

MongoDb ORM for PHP

Features: 

Easy Querying
---
Simple interface to quickly query a collection by it's short name, returning a Document or DocumentSet.

```php
$result = Epic_Mongo::db('shortname')->findOne();	// Returns Document
$results = Epic_Mongo::db('shortname')->find();	// Returns DocumentSet
```

Create Document Types
---
Create different document types with specific requirements, functionality and can be extended.

```php
// A 'user' that someone would be logged in as
class User_Class extends Epic_Mongo_Document {
	// The collection the documents are saved into
	protected static $_collectionName = 'users';
}

// A 'schema' is created for the connection to MongoDb
class Schema_Class extends Epic_Mongo_Schema {
	protected $_db = 'test_database';	// Defines which Database we use
	protected static $_typeMap = array(
		'user' => 'User_Class'	// This maps the 'shortname' of 'user' to the class 'User_Class'
	);
}
```

Easy Document Creation
---
Easily create a new document that is properly typed. 

```php
// Create a User 
$user = Epic_Mongo::new('user');	// The 'shortname' from the schema
$user->id = 1;
$user->username = 'admin';
$user->password = 'password';
$user->save();
```
Document Field Requirements
---
Create Requirements for specific fields on the Document Type

- 'Class:Name': (Optional) Forces the value of this field to be set to this Class when returned.
- AsReference: (Optional) Converts any Document passed into this field into a reference.
- Required: (Optional) Requires this field to be set in order to save.

```php
// A 'user' that someone would be logged in as
class User_Class extends Epic_Mongo_Document {
	// The collection the documents are saved into
	protected static $_collectionName = 'users';
}

// A 'post' that a user could create
class User_Post extends Epic_Mongo_Document {
	// The collection the documents are saved into
	protected static $_collectionName = 'posts';
	// Any requirements on fields for this document
	protected $_requirements = array(
		'author' => array('Class:User', 'AsReference', 'Required'),	
	);
}

// The schema must contain all of the different types
class Schema_Class extends Epic_Mongo_Schema {
	protected $_db = 'test_database';	// Defines which Database we use
	protected static $_typeMap = array(
		'user' => 'User_Class'	// This maps the 'shortname' of 'user' to the class 'User_Class'
		'post' => 'User_Post'	// This maps the 'shortname' of 'post' to the class 'User_Post'
	);
}

// Create a User 
$user = Epic_Mongo::new('user');	// The 'shortname' from the schema
$user->id = 2;
$user->username = 'author';
$user->password = 'password';
$user->save();

// Create a Post document for the User
$post = Epic_Mongo::new('post');

// Set the User as the author of the post
$post->author = $user;

// Set Extra 'post' information
$post->id = 1;
$post->title = 'Test Post';
$post->body = 'This is a test post, posted by User #1';
$post->created = time();

// Save the Post
$post->save();
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
When you pass in a full object, it will convert it to a reference per the requirements

```php
// Select User #1
$user = Epic_Mongo::db('user')->findOne(array('id' => 1));
// Build a Query for the posts collection where the author is a reference of the user
$query = array(
	'author' => $user,
);
// Find all posts
$posts = Epic_Mongo::db('post')->find($query);
```