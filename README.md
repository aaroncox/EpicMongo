Epic-Mongo
==========

MongoDb ORM for PHP

Features: 

Easy Querying
---
Simple interface to quickly query a collection by it's short name, returning a result set that can be iterated over.

```php
$results = Epic_Mongo::db('shortname')->find(array());
```

Create Document Types
---
Create different document types with specific requirements, functionality and are extendable.

```php
// A 'user' that someone would be logged in as
class User extends Epic_Mongo_Document {
	// The collection the documents are saved into
	protected static $_collectionName = 'users';
}
```

Easy Document Creation
---
Easily create a new document that is properly typed into the proper objects. 

```php
// Create a new Document 
$user = Epic_Mongo::new('user');
$user->id = 1;
$user->username = 'admin';
$user->password = 'password';
$user->save();
```
Automatic Reference Building
---
When you pass a Document into a field with a requirement of 'AsReference', it converts the Document to a DBRef

```php
// A 'post' that a user could create
class Post extends Epic_Mongo_Document {
	// The collection the documents are saved into
	protected static $_collectionName = 'posts';
	// Any requirements on fields for this document
	protected $_requirements = array(
		// The key, 'author', indicates which field on this document these parameters are targeting.
		// 'Class:Name' indicates that this field should returned as the type specified class.
		// 'AsReference' as a parameter is optional, but automatically converts the document in this field to a reference.
		// 'Required' as a parameter is optional, but automatically requires this field to be set in order to save.
		'author' => array('Class:User', 'AsReference', 'Required'),	
	);
}

// Grab User document with ID 1
$user = Epic_Mongo::db('user')->findOne(array('id' => 1));

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