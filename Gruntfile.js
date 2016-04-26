module.exports = function(grunt) {
    'use strict';
    require('load-grunt-tasks')(grunt, {
        pattern: ['grunt-*']
    });

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        config: {
            'cssSrcDir': 'src/sass',
            'cssTargetDir': 'css',
            'jsSrcDir': 'src/js',
            'jsTargetDir': 'js',
            'jsDependencies': [
                'app/webroot/js/lib/php.js',
                'app/webroot/js/lib/colr.js',
                'app/webroot/js/lib/ColorGenerator.js',
                'app/webroot/js/app/bootstrap.js',
                'app/webroot/js/vendor/jquery.blockUI.js',
                'app/webroot/js/lib/ui_blocker.js',
                'app/webroot/js/lib/dialog.js',
                'app/webroot/js/lib/jquery.hotkeys.js',
                'app/webroot/js/lib/jqconsole.min.js',
                'app/webroot/js/vendor/bootstrap.min.js',
                'app/webroot/js/vendor/chosen.jquery.min.js',
                'app/webroot/js/vendor/moment-with-locales.min.js',
                'app/webroot/js/vendor/fineuploader/jquery.fineuploader-3.2.js',
                'app/webroot/js/vendor/select2.min.js',
                'app/webroot/js/vendor/bootstrap-datepicker.js',
                'app/webroot/js/vendor/bootstrap-datetimepicker.js',
                'app/webroot/js/plugin/bootstrap-slider/bootstrap-slider.min.js',
                'app/webroot/js/plugin/bootstrap-tags/bootstrap-tagsinput.min.js',
                'app/webroot/smartadmin/js/notification/SmartNotification.js',
                'app/webroot/smartadmin/js/demo.js',
                'app/webroot/smartadmin/js/app.js',
                'app/webroot/smartadmin/js/smartwidgets/jarvis.widget.js',
                'app/webroot/smartadmin/js/plugin/easy-pie-chart/jquery.easy-pie-chart.min.js',
                'app/webroot/smartadmin/js/plugin/sparkline/jquery.sparkline.min.js',
                'app/webroot/smartadmin/js/plugin/flot/jquery.flot.cust.js',
                'app/webroot/smartadmin/js/plugin/jquery-validate/jquery.validate.min.js',
                'app/webroot/smartadmin/js/plugin/flot/jquery.flot.orderBar.js',
                'app/webroot/smartadmin/js/plugin/flot/jquery.flot.fillbetween.js',
                'app/webroot/smartadmin/js/plugin/flot/jquery.flot.pie.min.js',
                'app/webroot/smartadmin/js/plugin/flot/jquery.flot.resize.js',
                'app/webroot/smartadmin/js/plugin/flot/jquery.flot.navigate.js',
                'app/webroot/smartadmin/js/plugin/colorpicker/bootstrap-colorpicker.min.js',
                'app/webroot/js/app/shortcuts.js',
                'app/webroot/js/app/menuFilter.js',
                'app/webroot/smartadmin/js/plugin/dropzone/dropzone.min.js',
                'app/webroot/smartadmin/js/plugin/datatables/jquery.dataTables.js',
                'app/webroot/smartadmin/js/plugin/datatables/dataTables.bootstrap.min.js',
                'app/webroot/smartadmin/js/plugin/datatables/dataTables.colReorder.min.js',
                'app/webroot/smartadmin/js/plugin/datatables/dataTables.colVis.min.js',
                'app/webroot/smartadmin/js/plugin/datatables/dataTables.tableTools.min.js',
                'app/webroot/smartadmin/js/plugin/datatables/fnPagingInfo.js',
                'app/webroot/smartadmin/js/plugin/fullcalendar-2.3.1/fullcalendar.min.js',
                'app/webroot/smartadmin/js/plugin/fuelux/wizard/wizard.js',
                'app/webroot/js/lib/jquery-cookie.js',
                'app/webroot/js/lib/jquery.nestable.js',
                'app/webroot/js/lib/jquery-jvectormap-1.2.2.min.js',
                'app/webroot/js/lib/maps/jquery-jvectormap-world-mill-en.js',
                'app/webroot/js/lib/jquery-ui-1.11.2.min.js',
                'app/webroot/js/lib/rangyinputs-jquery-1.1.2.min.js',
                'app/webroot/js/lib/jquery.imgareaselect-0.9.10/scripts/jquery.imgareaselect.min.js',
                'app/webroot/js/lib/jquery.svg.min.js',
                'app/webroot/js/vendor/bootbox.min.js',
                'app/webroot/js/lib/jquery.svgfilter.min.js',
                'app/webroot/js/vendor/jquery.qrcode.min.js',
                'app/webroot/js/vendor/jquery.blockUI.js',
                'app/webroot/js/vendor/prism.js',
                'app/webroot/js/vendor/gauge/gauge.min.js',
                'app/webroot/js/vendor/lodash/lodash.min.js',
                'app/webroot/js/vendor/gridstack/dist/gridstack.min.js'
            ],
            'cssDependencies': [
                'app/webroot/css/lib/jquery-jvectormap-1.2.2.css',
                'app/webroot/css/lib/lib/jquery.imgareaselect-0.9.10/imgareaselect-animated.css',
                'app/webroot/css/lib/lib/jquery.svg.css',
                'app/webroot/css/vendor/bootstrap/css/bootstrap.min.css',
                'app/webroot/css/vendor/chosen/chosen.css',
                'app/webroot/css/vendor/chosen/chosen-bootstrap.css',
                'app/webroot/css/list_filter.css',
                'app/webroot/css/vendor/fineuploader/fineuploader-3.2.css',
                'app/webroot/css/vendor/select2/select2.css',
                'app/webroot/css/vendor/select2/select2-bootstrap.css',
                'app/webroot/css/vendor/bootstrap-datepicker.css',
                'app/webroot/css/vendor/bootstrap-datetimepicker.min.css',
                'app/webroot/smartadmin/css/font-awesome.min.css',
                'app/webroot/smartadmin/css/smartadmin-production.min.css',
                'app/webroot/smartadmin/css/smartadmin-production-plugins.min.css',
                'app/webroot/smartadmin/css/smartadmin-skins.css',
                'app/webroot/smartadmin/css/demo.css',
                'app/webroot/smartadmin/css/your_style.css',
                'app/webroot/smartadmin/css/animate.css',
                'app/webroot/css/lockscreen.css',
                'app/webroot/css/base.css',
                'app/webroot/css/app.css',
                'app/webroot/css/status.css',
                'app/webroot/css/lists.css',
                'app/webroot/css/ansi.css',
                'app/webroot/css/console.css',
                'app/webroot/css/vendor/prism.css',
                'app/webroot/css/vendor/gridstack/gridstack.min.css',
                'app/webroot/smartadmin/js/plugin/fullcalendar-2.3.1/fullcalendar.min.css'
            ]
        },
        clean: {
            dist: ['static']
        },
        cssmin: {
            dev: {
                options: {
                    shorthandCompacting: false,
                    roundingPrecision: -1,
                    sourceMap: true
                },
                files: {
                    'app/webroot/<%=  config.cssTargetDir %>/app_build.css': [
                        '<%=	config.cssDependencies %>'
                    ]
                }
            },
            dist: {
                options: {
                    shorthandCompacting: false,
                    roundingPrecision: -1,
                    sourceMap: false
                },
                files: {
                    'app/webroot/<%= config.cssTargetDir %>/assets.css': [
                        '<%= config.cssDependencies %>'
                    ]
                }
            }
        },
        postcss: {
            options: {
                map: true,
                processors: [
                    require('autoprefixer')({
                        browsers: ['last 2 versions']
                    })
                ]
            },
            files: {
                src: 'app/webroot/<%=  config.cssTargetDir %>/*.css'
            }
        },
        uglify: {
            dev: {
                files: {
                    'app/webroot/<%= config.jsTargetDir %>/script.js': [
                        '<%= config.jsSrcDir %>/**/*.js'
                    ],
                    'app/webroot/<%= config.jsTargetDir %>/app_build.js': [
                        '<%= config.jsDependencies %>'
                    ]
                }
            },
            devlight: {
                files: {
                    'app/webroot/<%= config.jsTargetDir %>/script.js': [
                        '<%= config.jsSrcDir %>/**/*.js'
                    ]
                }
            },
            dist: {
                files: {
                    'app/webroot/<%= config.jsTargetDir %>/script.js': [
                        '<%= config.jsSrcDir %>/**/*.js'
                    ],
                    'app/webroot/<%= config.jsTargetDir %>/assets.js': [
                        '<%= config.jsDependencies %>'
                    ]
                }
            }
        },
        watch: {
            css: {
                files: '<%=  config.cssSrcDir %>/**/*.scss',
                tasks: ['copy:dev', 'postcss']
            },
            js: {
                files: '<%=  config.jsSrcDir %>/**/*.js',
                tasks: ['uglify:devlight']
            }
        }
    });

    grunt.registerTask('build', [
        'clean:dist',
        'cssmin:dist',
        'postcss',
        'uglify:dist'
    ]);
    grunt.registerTask('default', [
        'cssmin:dev',
        'postcss',
        'uglify:dev',
        'watch'
    ]);
};