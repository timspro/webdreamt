module.exports = function (grunt) {

	var copy = [];
	var targets = ['css', 'less', 'img', 'js', 'fonts'];
	for (var i = 0; i < targets.length; i++) {
		var name = targets[i];
		copy.push({
			flatten: true,
			expand: true,
			src: ["**"],
			dest: name,
			cwd: "build/" + name,
			filter: 'isFile'
		});
	}

	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		cssmin: {
			do: {
				files: {
					'dist/build.min.css': ['css/bootstrap.css', 'css/bootstrap-datetime.css',
						'css/select2.css', 'css/select2-bootstrap.css', 'css/webdreamt.css']
				}
			}
		},
		concat: {
			options: {
				separator: ';'
			},
			dist: {
				src: ['js/jquery.js', 'js/boostrap.js', 'js/moment.js', 'js/*.js'],
				dest: 'dist/build.js'
			}
		},
		uglify: {
			options: {
				banner: '/*! Built for <%= pkg.name %> on <%= grunt.template.today("dd-mm-yyyy") %> */\n'
			},
			dist: {
				files: {
					'dist/build.min.js': ['<%= concat.dist.dest %>']
				}
			}
		},
		copy: {
			do: {
				files: copy
			}
		},
		watch: {
			do: {
				files: ['Gruntfile.js', 'js/*.js', 'css/*.css'],
				tasks: ['default'],
				options: {
					livereload: true
				}
			}
		}
	});
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-contrib-concat');
	grunt.loadNpmTasks('grunt-contrib-cssmin');
	grunt.loadNpmTasks('grunt-contrib-copy');
	grunt.registerTask('guard', ['watch:do']);
	grunt.registerTask('default', ['concat', 'cssmin:do']);
	grunt.registerTask('setup', ['copy:do']);
	grunt.registerTask('minimize', ['uglify']);
};