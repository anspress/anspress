const sass = require('sass');
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
            '!vendor/**',
            '!tests/**',
          ],
          dot: false,
        },],
      },
    },
  });

  grunt.registerTask('translate', ['makepot']);
  grunt.registerTask('build', ['translate', 'wp_readme_to_markdown']);
  grunt.registerTask('default', ['build']);

}
