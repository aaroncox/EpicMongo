module.exports = function(grunt) {
	
	grunt.initConfig({
		watch: {
			files: grunt.file.expandFiles("**/*.php"),
			tasks: "test"
		}
	});
	
	grunt.registerTask( 'test' , function () { 
		var done = this.async();
		grunt.utils.spawn({
			cmd: "phpunit"
		}, function(err, result) { 
			if ( err ) {
				console.log( err.stdout , err.stderr );
				grunt.log.error( 'PHPUnit Failed' );
				return done( err );
			}

			grunt.log.writeln( result );

			done();			
		});
	});
	
	grunt.registerTask( 'default' , 'test' );
	
}