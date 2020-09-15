	module.exports = function (grunt) {
		require('load-grunt-tasks')(grunt);

		grunt.initConfig({
			pkg: grunt.file.readJSON('package.json'),

			dirs: {
				lang: 'languages',
			},
			makepot: {
				target: {
					options: {
						domainPath: '/languages',
						exclude: ['.git/.*', '.svn/.*', 'node_modules/.*', 'vendor/.*'],
						mainFile: 'anspress-question-answer.php',
						potHeaders: {
							poedit: true,
							'x-poedit-keywordslist': true
						},
						type: 'wp-plugin',
						updateTimestamp: true,
						updatePoFiles: true
					}
				}
			},
			csscomb: {
				files: ['**/*.css', '!**/node_modules/**'],
				tasks: ['csscomb'],
			},
			version: {
				options: {
					release: 'patch'
				},
				css: {
					options: {
						prefix: 'Version\\:\\s'
					},
					src: ['style.css'],
				},
				php: {
					options: {
						prefix: 'Version\\:\\s+'
					},
					src: ['anspress-question-answer.php'],
				},
				mainplugin: {
					options: {
						pattern: '\$_plugin_version = (?:\')(.+)(?:\')/g'
					},
					src: ['anspress-question-answer.php'],
				},
				project: {
					src: ['package.json']
				},
				readme: {
					options: {
						prefix: 'Stable tag:\\s+'
					},
					src: ['readme.txt']
				}
			},
			sass: {
				dist: {
					options: {
						style: 'compressed',
						sourcemap: 'auto'
					},
					files: {
						"templates/css/main.css": "templates/scss/main.scss",
						"templates/css/fonts.css": "templates/scss/fonts.scss",
						"templates/css/RTL.css": "templates/scss/RTL.scss",
						"assets/ap-admin.css": "assets/ap-admin.scss",
						"templates/addons/email/style.css": "templates/addons/email/style.scss"
					}
				}
			},
			uglify: {
				options: {
					sourceMap: true
				},
				my_target: {
					files: {
						'assets/js/min/main.min.js': ['assets/js/common.js', 'assets/js/question.js', 'assets/js/ask.js', 'assets/js/list.js', 'assets/js/notifications.js'],
						'assets/js/min/ap-admin.min.js': ['assets/js/ap-admin.js'],
						'assets/js/min/admin-app.min.js': ['assets/js/admin-app.js'],
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
			compress: {
				plugin: {
					options: {
						archive: 'build/anspress.zip'
					},
					files: [{
						expand: true,
						src: [
							'**/*',
							'!.',
							'!build/**',
							'!node_modules/**',
						],
						dot: false,
					}, ],
				},
			},

			watch: {
				sass: {
					files: ['**/*.scss'],
					tasks: ['sass'],
				},
				uglify: {
					files: ['templates/js/*.js', 'assets/js/*.js'],
					tasks: ['uglify'],
				}
			}
		});

		grunt.registerTask('translate', ['makepot']);
		grunt.registerTask('build', ['sass', 'uglify', 'translate']);

	}