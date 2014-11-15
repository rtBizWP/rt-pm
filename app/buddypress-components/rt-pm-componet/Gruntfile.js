'use strict';
module.exports = function(grunt) {

    // load all grunt tasks matching the `grunt-*` pattern
    // Ref. https://npmjs.org/package/load-grunt-tasks
    require('load-grunt-tasks')(grunt);

    grunt.initConfig({

        // SCSS and Compass
        // Ref. https://npmjs.org/package/grunt-contrib-compass
        compass: {
            frontend: {
                options: {
                    config: 'config.rb',
                    force: true
                }
            }
        },

        // Uglify
        // Compress and Minify JS files in js/rtp-main-lib.js
        // Ref. https://npmjs.org/package/grunt-contrib-uglify
        uglify: {
            options: {
                banner: '/*! \n * rtPanel JavaScript Library \n * @package rtPanel \n */'
            },
            frontend: {
				src: [
					'assets/foundation/bower_components/modernizr/modernizr.js',
					'assets/foundation/bower_components/foundation/js/foundation.min.js',
                    'assets/js/rt-bp-person.js'
				],
				dest: 'assets/javascripts/rt-bp-people.min.js'
			},

        },

        // Watch for hanges and trigger compass and uglify
        // Ref. https://npmjs.org/package/grunt-contrib-watch
        watch: {
            compass: {
                files: ['assets/scss/*.{scss,sass}', 'assets/foundation/bower_components/foundation/scss/foundation/components/*.{scss,sass}'],
                tasks: ['compass']
            },

//            uglify: {
//                files: ['<%= uglify.frontend.src %>'],
//                tasks: ['uglify']
//            },

            livereload: {
                options: {livereload: true},
                files: ['css/*.css', 'js/*.js', '*.html', '*.php', 'img/**/*.{png,jpg,jpeg,gif,webp,svg}']
            }
        }
    });

	// Imagemin Task
	// grunt.registerTask('default', ['imagemin']);

    // WordPress Deploy Task
    // grunt.registerTask('default', ['wordpressdeploy']);

    // Register Task
    grunt.registerTask('default', ['watch', 'compass'] );
};