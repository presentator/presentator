module.exports = function(grunt) {
    var sass_options = {
        outputStyle: 'compressed', //'nested', 'expanded', 'compact', 'compressed'
        sourceComments: false, //'none', 'normal', 'map'
    };

    var gruntConfig = {
        apidoc: {
            dev: {
                src:  ['docs', 'api/controllers'],
                dest: 'api/web/doc-dev',
                template: 'api/web/apidoc-template',
                options: {
                    packageInfo: {
                        // update accordingly
                        "url":       "http://api.presentator.dev",
                        "sampleUrl": "http://api.presentator.dev"
                    }
                }
            },
            prod: {
                src:  ['docs', 'api/controllers'],
                dest: 'api/web/doc',
                template: 'api/web/apidoc-template',
                options: {
                    packageInfo: {
                        // update accordingly
                        "url":       "https://api.presentator.io",
                        "sampleUrl": "https://api.presentator.io"
                    }
                }
            }
        },
        watch: {
            css: {
                files: '**/*.scss',
                tasks: ['sass']
            },
            scripts: {
                files: [
                    'docs/*.js',
                    'api/controllers/*.php'
                ],
                tasks: ['apidoc'],
                options: {
                    spawn: false,
                },
            },
        },
        sass: {
            // main app CSS
            app: {
                options: sass_options,
                files: {'app/web/css/style.css': [
                    'app/web/scss/style.scss'
                ]}
            },
        },
        uglify: {
            app: {
                options: {
                    sourceMap: true,
                    compress: false,
                    report: 'none',
                },
                files: {
                    'app/web/js/all.min.js': [
                    ]
                }
            }
        },
    };

    grunt.initConfig(gruntConfig);

    grunt.loadNpmTasks('grunt-sass');
    grunt.loadNpmTasks('grunt-apidoc');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-watch');

    grunt.registerTask('default', ['apidoc', 'sass', 'uglify', 'watch']);
    grunt.registerTask('css', ['sass']);
    grunt.registerTask('js', ['uglify']);
};
