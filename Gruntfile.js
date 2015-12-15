module.exports = function(grunt) {
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-contrib-less');
	grunt.loadNpmTasks('grunt-wp-i18n' );
	grunt.loadNpmTasks('grunt-phpdocumentor');
	grunt.loadNpmTasks('grunt-csscomb');
	grunt.loadNpmTasks('grunt-contrib-copy');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-wp-readme-to-markdown');
	grunt.loadNpmTasks('grunt-version');
	grunt.loadNpmTasks('grunt-git');
	grunt.loadNpmTasks('grunt-phplint');
	grunt.loadNpmTasks('grunt-contrib-compress');

	grunt.initConfig({
		pkg: grunt.file.readJSON( 'package.json' ),
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
	    
	    addtextdomain: {
	    	options: {
	            textdomain: 'anspress-question-answer',    // Project text domain.
	            updateDomains: ['ap']  // List of text domains to replace.
	        },
	        target: {
	            files: {
	                src: [
	                    '*.php',
	                    '**/*.php',
	                    '!node_modules/**',
	                    '!tests/**',
	                    '!.git/.*', '!.svn/.*', '!.vendor/.*'
	                ]
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
	    	files: ['**/*.css', '!**/node_modules/**'],
	    	tasks: ['csscomb'],
	    },

	    copy: {
	    	main: {
	    		files: [
	    		{nonull:true, expand: true, cwd: 'M:\\wamp\\www\\anspress\\wp-content\\plugins\\anspress-question-answer', src: ['**/*', '!**/.git/**', '!**/.svn/**', '!**/node_modules/**', '!**/bin/**', '!**/docs/**', '!**/tests/**'], dest: 'M:\\wamp\\www\\aptest\\wp-content\\plugins\\anspress-question-answer'},
	    		{nonull:true, expand: true, cwd: 'M:\\wamp\\www\\anspress\\wp-content\\plugins\\anspress-question-answer', src: ['**/*', '!**/.git/**', '!**/.svn/**', '!**/node_modules/**', '!**/bin/**', '!**/docs/**', '!**/tests/**'], dest: 'M:\\wamp\\www\\answerbox\\wp-content\\plugins\\anspress-question-answer'}
	    		]
	    	}
	    },
	    version: {
	    	css: {
	    		options: {
	    			prefix: 'Version\\:\\s'
	    		},
	    		src: [ 'style.css' ],
	    	},
	    	php: {
	    		options: {
	    			prefix: 'Version\\:\\s+'
	    		},
	    		src: [ 'anspress-question-answer.php' ],
	    	},
	    	mainplugin: {
	    		options: {
	    			pattern: '\$_plugin_version = (?:\')(.+)(?:\')/g'
	    		},
	    		src: [ 'anspress-question-answer.php' ],
	    	},
	    	project: {
	    		src: ['plugin.json']
	    	}
	    },
	    less: {
	    	main: {
	    		options: {
	    			paths: ["less"]
	    		},
	    		files: {
	    			"theme/default/css/main.css": "theme/default/less/main.less",
	    			"theme/default/css/responsive.css": "theme/default/less/responsive.less",
	    			"assets/ap-admin.css": "assets/ap-admin.less"
	    		}
	    	},
	    },
	    uglify: {
	    	my_target: {
	    		files: {
	    			'assets/min/anspress_site.min.js': ['assets/js/anspress_site.js'],
	    			'assets/min/ap-functions.min.js': ['assets/js/ap-functions.js'],
	    			'assets/min/ap-admin.min.js': ['assets/js/ap-admin.js'],
	    			'theme/default/min/ap.min.js': ['theme/default/js/ap.js']
	    		}
	    	}
	    },
	    wp_readme_to_markdown: {
	    	your_target: {
	    		files: {
	    			'README.md': 'readme.txt'
	    		},
	    	},
	    },

	    compress: {
	    	main: {
	    		options: {
	    			archive: 'build/anspress-question-answer.zip'
	    		},
		        //cwd: 'build/',
		        expand: true,
		        src: ['**','!**/tests/**','!**/node_modules/**','!**/.git/**','!**/.svn/**','!**/.gitignore','!**/.scrutinizer.yml','!**/.scrutinizer.yml','!**/.travis.yml','!**/npm-debug.log','!**/phpdoc.dist.xml','!**/phpunit.xml','!**/plugin.json','!**/tasks.TODO','!**/build']
		    }
		},

		phplint : {
			options : {
				spawn : false
			},
			all: ['**/*.php']
		},
		
		watch: {
			less: {
				files: ['**/*.less'],
				tasks: ['less'],
			},
			uglify: {
				files: ['theme/default/js/*.js','assets/js/*.js'],
				tasks: ['uglify'],
			}
		},
	});

grunt.registerTask( 'build', [ 'phplint', 'wp_readme_to_markdown', 'makepot', 'version', 'less', 'uglify', 'compress' ]);

}
