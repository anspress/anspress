module.exports = function(grunt) {
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks( 'grunt-wp-i18n' );
	grunt.loadNpmTasks('grunt-phpdocumentor');

	grunt.initConfig({
	    makepot: {
	        target: {
	            options: {
	                //cwd: '',                          // Directory of files to internationalize.
	                //domainPath: '',                   // Where to save the POT file.
	                //exclude: [],                      // List of files or directories to ignore.
	                //include: [],                      // List of files or directories to include.
	                mainFile: 'anspress.php',                     // Main project file.
	                //potComments: '',                  // The copyright at the beginning of the POT file.
	                //potFilename: '',                  // Name of the POT file.
	                potHeaders: {
	                    poedit: true,                 // Includes common Poedit headers.
	                    'x-poedit-keywordslist': true // Include a list of all possible gettext functions.
	                },                                // Headers to add to the generated POT file.
	                //processPot: null,                 // A callback function for manipulating the POT file.
	                type: 'wp-plugin',                // Type of project (wp-plugin or wp-theme).
	                updateTimestamp: true             // Whether the POT-Creation-Date should be updated without other changes.
	            }
	        }
	    },
	    phpdocumentor: {
	        dist: {
	            options: {
	                directory : './',
	                target : 'docs',
	                ignore: 'node_modules'
	            }
	        }
	    },
		watch: {
			makepot: {
				files: ['**/*.php'],
				tasks: ['makepot'],
			}
		},
	});

}