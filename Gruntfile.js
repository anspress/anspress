module.exports = function(grunt) {
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-contrib-less');
	grunt.loadNpmTasks( 'grunt-wp-i18n' );
	grunt.loadNpmTasks('grunt-phpdocumentor');
	grunt.loadNpmTasks('grunt-csscomb');
	grunt.loadNpmTasks('grunt-contrib-copy');

	grunt.initConfig({
	    makepot: {
	        target: {
	            options: {
	                //cwd: '',                          // Directory of files to internationalize.
	                domainPath: '/languages',                   // Where to save the POT file.
	                exclude: ['.git/.*', '.svn/.*', '.node_modules/.*', '.vendor/.*'],
	                //include: [],                      // List of files or directories to include.
	                mainFile: 'anspress-question-answer.php',                     // Main project file.
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
	                target : 'E:\\wamp\\www\\anspress-doc',
	                ignore: 'node_modules'
	            }
	        }
	    },
	    csscomb: {
	        files: ['**/*.css'],
			tasks: ['csscomb'],
	    },

		copy: {
		    main: {
		        files: [
		            {nonull:true, expand: true, cwd: 'E:\\wamp\\www\\anspress\\wp-content\\plugins\\anspress-question-answer', src: ['**/*', '!**/.git/**', '!**/.svn/**', '!**/node_modules/**', '!**/bin/**', '!**/docs/**', '!**/tests/**'], dest: 'E:\\wamp\\www\\answerbox\\wp-content\\plugins\\anspress-question-answer'}
		        ]
		    }
		},
		less: {
		  development: {
		    options: {
		      paths: ["less"]
		    },
		    files: {
		      "theme/default/css/main.css": "theme/default/less/main.less",
		    }
		  },
		  production: {
		    files: {
		      "theme/default/css/main.css": "theme/default/less/main.less",

		    }
		  }
		},
		watch: {
			less: {
				files: ['**/*.less'],
				tasks: ['less'],
			}
		},
	});

}