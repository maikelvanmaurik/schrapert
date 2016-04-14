var exec = require('child_process').exec,
    spawn = require('child_process').spawn,
    async = require('async'),
    os = require('os'),
    fs = require('fs'),
    q = require('q'),
    semver = require('semver'),
    glob = require('glob'),
    s = require('underscore.string'),
    isWin = /^win/.test(process.platform),
    xpath = require('xpath'),
    dom = require('xmldom').DOMParser,
    APPLICATION_ENV = process.env.APPLICATION_ENV || 'PRODUCTION',
    COMPOSER_BIN_DIR = process.env.COMPOSER_BIN_DIR || __dirname + '/vendor/bin';

console.log(COMPOSER_BIN_DIR);


String.prototype.toCamel = function () {
    return this.replace(/(\-[a-z])/g, function ($1) {
        return $1.toUpperCase().replace('-', '');
    });
};

function firstExistingFile() {
    var path;
    for(var i = 0; i < arguments.length; i++) {
        path = arguments[i];
        try {
            if (fs.lstatSync(path).isFile()) {
                return path;
            }
        } catch(e) {

        }
    }
    return null;
}

module.exports = function (grunt) {
    grunt.initConfig({
        directories: {
            composerBin: COMPOSER_BIN_DIR,
            assets: 'app/assets',
            bower: __dirname + '/app/assets/bower_components'
        },
        pkg: grunt.file.readJSON('package.json'),
        php: {
            develop: {
                options: {
                    base: './app',
                    port: 3000,
                    keepalive: true,
                    open: true,
                    router: './../dev-server.php'
                }
            }
        },
        copy: {
            cssAsScss: {
                files: [
                    {
                        expand: true,
                        cwd: '<%= directories.bower %>',
                        src: ['**/*.css', '!**/*.min.css'],
                        dest: '<%= directories.bower %>',
                        filter: 'isFile',
                        ext: ".scss"
                    }
                ]
            },
            images: {
                files: [{
                    expand: true,
                    cwd: '<%= directories.bower %>',
                    src: ['**/*.png', '**/*.jpg', '**/*.gif'],
                    dest: 'app/assets/images/vendor',
                    filter: 'isFile'
                }]
            }
        },
        "integration-test": {
            options: {
                base: ['integration']
            },
            "wordpress-communication": {
                environment: ["wordpress"],
                suite: ['wordpress-communication']
            },
            "blog-site-appearance": {
                environment: ["wordpress-integration-environment", "selenium-server", "project-oxford-proxy"],
                suite: ['blog-site-appearance']
            }
        },
        phpunit: {
            integration: {
                testsuite: 'integration'
            },
            unit: {
                testsuite: 'unit'
            },
            options: {
                bin: '<%= directories.composerBin %>/phpunit',
                configuration: firstExistingFile(__dirname + "/phpunit.xml", __dirname + "/phpunit.xml.dist"),
                staticBackup: false,
                colors: true,
                verbose: true,
                debug: true,
                noGlobalsBackup: false
            }
        },
        git_changelog: {
            basic: {
                options: {
                    appName: 'Cluster',
                    file: './CHANGELOG.md',
                    grep_commits: '^fix|^feat|^refactor|^chore|BREAKING',
                    tag: 'v1.0.0',
                    debug: true,
                    extended: true,
                    version: '1.0'
                }
            }
        },
        compass: {
            dist: {
                options: {
                    sassDir: '<%= directories.assets %>/scss',
                    cssDir: '<%= directories.assets %>/css',
                    fontsDir: '<%= directories.assets %>/css/fonts',
                    imagesPath: '<%= directories.assets %>/images',
                    imagesDir: 'app',
                    //httpImagesPath: '../DERP/images/',
                    generatedImagesDir: '<%= directories.assets %>/images',
                    //importPath: ['bower_components/foundation/scss'], // additional include paths
                    outputStyle: 'expanded', // nested/expanded/compact/compressed
                    relativeAssets: true,
                    require: ['bootstrap-sass']
                }
            }
        },

        changelog: {
            full: {
                target: './CHANGELOG.md',
                suffix: 'Bla'
            }
        },
        watch: {
            grunt: {files: ['Gruntfile.js']},

            js: {
                files: ['<%= directories.assets %>/js/src/**/*.js'],
                tasks: ['requirejs']
            },

            css: {
                files: ['<%= directories.assets %>/scss/**/*.scss'],
                tasks: ['compass', 'cssmin']
            },

            php: {
                files: ['src/**/*.php', 'tests/**/*.php'],
                tasks: ['phpunit']
            },

            tdd: {
                files: ['tests/**/*.php', '!tests/phpunit/etc/*.php', '!app/config/classmap.php', 'app/**/*.php', 'core/libraries/**/*.php', '!tests/integration/servers/**/*.*'],
                tasks: ['phpunit']
            },
            "tdd-integration": {
                files: ['tests/integration/**/*.php', '!tests/phpunit/etc/*.php', '!app/config/classmap.php', 'app/**/*.php'],
                tasks: ['phpunit:integration']
            },
            peg: {
                files: ['**/*.peg.inc'],
                tasks: ['compile-peg-files']
            }
        }
    });

    // Register tasks defined as dependencies in composer.json
    require('load-grunt-tasks')(grunt);

    function php_exec(cmd, cb) {
        cmd = 'php ' + cmd;
        exec(cmd, function (error, stdout, stderr) {
            if (error != null) {
                grunt.log.error(error);
            }
            cb(error, stdout, stderr);
        });
    }

    grunt.registerTask('install-git-hooks', function () {
        var done = this.async();
        grunt.log.ok("Installing git hooks");

        fs.readFile(__dirname + '/git-hooks/validate-commit-message.js', function (err, data) {
            if (err) {
                grunt.log.error('Error reading file ' + err);
                done(false);
            }
            fs.writeFile('.git/hooks/commit-msg', '' + data, function (err) {
                if (err) {
                    grunt.log.error('Error writing file');
                    done(false);
                }
                fs.chmod('.git/hooks/commit-msg', 0777, function (err) {
                    if (err) {
                        grunt.log.error('Error changing permissions ' + err);
                        done(false);
                    }
                    done();
                })
            });
        });
    });

    grunt.registerTask('tdd', ['watch:tdd']);

    var getVersions = function () {
        var deferred = q.defer();

        exec('git tag -l', function (err, stdout) {
            var versions = stdout.split('\n').filter(function (tag) {
                return tag[0] == 'v' && semver.valid(tag);
            }).sort(function (a, b) {
                return semver.cmp(a, '<', b);
            });

            deferred.resolve(versions);
        });

        return deferred.promise;
    };

    var getLatestVersion = function () {
        var deferred = q.defer();

        getVersions().then(function (versions) {
            deferred.resolve(versions[0]);

        });

        return deferred.promise;
    }

    var generateChangeLog = function (version) {

        return function (output) {

            var deferred = q.defer(), tmpFile = s.rtrim(os.tmpdir(), '/') + '/' + version + (new Date()).getTime();
            exec('node changelog.js ' + version + ' ' + tmpFile, function (code, stdout) {

                grunt.log.warn(stdout);

                if (code) {
                    deferred.reject(new Error(code));
                }

                fs.exists(tmpFile, function (exists) {
                    if (exists) {
                        fs.readFile(tmpFile, function (err, data) {
                            if (err) deferred.reject(err);
                            output.push('' + data);
                            deferred.resolve(output);
                        });
                    } else {
                        deferred.resolve(output);
                    }
                });

            });

            return deferred.promise;
        };
    };

    var updatePackageJsonVersion = function (version) {
        var deferred = q.defer();

        fs.readFile(__dirname + '/package.json', function (err, content) {
            if (err) {
                deferred.reject(new Error(err));
            }

            var json = JSON.parse(content);

            json.version = version;

            fs.writeFile(__dirname + '/package.json', JSON.stringify(json, null, "\t"), function (err) {
                if (err) {
                    deferred.reject(new Error(err));
                }
                deferred.resolve(version);
            });
        });

        return deferred.promise;
    };

    grunt.registerTask('update-package-info', 'Updates the package files', function () {
        var done = this.async();
        getLatestVersion().then(function (version) {
            return updatePackageJsonVersion(version.substr(1))
        }).then(done);
    });

    grunt.registerMultiTask('changelog', function () {
        var done = this.async(), config = this.data;

        getVersions().then(function (versions) {
            var stack = [];
            versions.forEach(function (version) {
                stack.push(generateChangeLog(version));
            });

            stack.push(function (output) {
                fs.writeFile(config.target, output.join('\n'), done);
            });

            return stack.reduce(function (soFar, f) {
                return soFar.then(f, function (err) {
                    grunt.log.error(err);
                    done(false);
                });
            }, q([]));

        }).catch(function (error) {
            grunt.log.error(error);
            done(false);
        });
    });

    grunt.registerTask('develop', ['install-git-hooks', 'php:develop']);
    grunt.registerTask('build', ['phpclassmap', 'copy:cssAsScss', 'compass', 'cssmin', 'requirejs', 'uglify']);
    grunt.registerTask('default', ['build', 'watch']);
    grunt.registerTask('production-update', ['db:migrate', 'build', 'changelog', 'update-package-info']);
}