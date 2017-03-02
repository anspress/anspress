	module.exports = function(grunt) {
		require('load-grunt-tasks')(grunt);

		grunt.initConfig({
			pkg: grunt.file.readJSON( 'package.json' ),
			makepot: {
				target: {
					options: {
						domainPath: '/languages',                   // Where to save the POT file.
						exclude: ['.git/.*', '.svn/.*', '.node_modules/.*', '.vendor/.*'],
						mainFile: 'anspress-question-answer.php',
						potHeaders: {
								poedit: true,                 // Includes common Poedit headers.
								'x-poedit-keywordslist': true // Include a list of all possible gettext functions.
						},                                // Headers to add to the generated POT file.
						type: 'wp-plugin',                // Type of project (wp-plugin or wp-theme).
						updateTimestamp: true             // Whether the POT-Creation-Date should be updated without other changes.
					}
				}
			},
			phpdocumentor: {
				dist: {
					options: {
						directory : './',
						target : 'M:\wamp\www\anspress-docs\\'
					}
				}
			},
			csscomb: {
				files: ['**/*.css', '!**/node_modules/**'],
				tasks: ['csscomb'],
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
					src: ['package.json']
				}
			},
			sass: {
				dist: {
					options: {
						style: 'expanded'
					},
					files: {
						"templates/css/main.css": "templates/scss/main.scss",
						"templates/css/RTL.css": "templates/scss/RTL.scss",
						"assets/ap-admin.css": "assets/ap-admin.scss"
					}
				}
			},
			uglify: {
				my_target: {
					files: {
						'assets/js/min/question.min.js': ['assets/js/question.js'],
						'assets/js/min/common.min.js': ['assets/js/common.js'],
						'assets/js/min/upload.min.js': ['assets/js/upload.js'],
						'assets/js/min/ap-admin.min.js': ['assets/js/ap-admin.js'],
						'assets/js/min/ask.min.js': ['assets/js/ask.js'],
						'assets/js/min/list.min.js': ['assets/js/list.js'],
						'assets/js/min/tags.min.js': ['assets/js/tags.js'],
						'assets/js/min/notifications.min.js': ['assets/js/notifications.js'],
						'templates/js/min/theme.min.js': ['templates/js/theme.js'],
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

		phplint : {
			options : {
				spawn : false
			},
			all: ['**/*.php']
		},
		cssmin: {
			options: {
				shorthandCompacting: false,
				roundingPrecision: -1,
				rebase: true
			},
			target: {
				files: {
					'templates/css/min/main.min.css': 'templates/css/main.css',
					'templates/css/min/RTL.min.css': 'templates/css/RTL.css',
					'templates/css/min/fonts.min.css': 'templates/css/fonts.css',
					'assets/ap-admin.min.css': 'assets/ap-admin.css'
				}
			}
		},

		watch: {
			sass: {
				files: ['**/*.scss'],
				tasks: ['sass', 'cssmin'],
			},
			uglify: {
				files: ['templates/js/*.js','assets/js/*.js'],
				tasks: ['uglify'],
			}
		},
	});

	grunt.registerTask( 'build', [ 'phplint', 'makepot', 'version', 'sass', 'uglify', 'compress' ]);

}
