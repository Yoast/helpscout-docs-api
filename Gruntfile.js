var path = require( "path" );
var loadGruntConfig = require( "load-grunt-config" );
var timeGrunt = require( "time-grunt" );
global.developmentBuild = true;

/* global global, require, process */
module.exports = function( grunt ) {
	timeGrunt( grunt );

	const pkg = grunt.file.readJSON( "package.json" );
	const pluginVersion = pkg.yoast.pluginVersion;

	// Define project configuration
	var project = {
		pluginVersion: pluginVersion,
		pluginSlug: "helpscout-docs-api",
		pluginMainFile: "helpscout-docs-api.php",
		pluginVersionConstant: "HS_DOCS_API_PLUGIN_VERSION",
		paths: {
			/**
			 * Get config path.
			 *
			 * @returns {string} config path.
			 */
			get config() {
				return this.grunt + "config/";
			},
			css: "css/dist/",
			sass: "css/src/",
			grunt: "grunt/",
			assets: "svn-assets/",
			js: "js/",
			languages: "languages/",
			logs: "logs/",
			vendor: "vendor/",
			svnCheckoutDir: ".wordpress-svn",
		},
		files: {
			css: [
				"css/dist/*.css",
				"!css/dist/*.min.css",
			],
			sass: [
				"css/src/*.scss",
			],
			js: [
				"js/*.js",
				"!js/*.min.js",
			],
			php: [
				"*.php",
				"admin/**/*.php",
				"frontend/**/*.php",
				"includes/**/*.php",
			],
			phptests: "tests/**/*.php",
			/**
			 * Gets config path glob.
			 *
			 * @returns {string} Config path glob.
			 */
			get config() {
				return project.paths.config + "*.js";
			},
			/**
			 * Gets changelog path.
			 *
			 * @returns {string} Changelog path.
			 */
			get changelog() {
				return project.paths.theme + "changelog.txt";
			},
			grunt: "Gruntfile.js",
			artifact: "artifact",
			artifactComposer: "artifact-composer",
		},
		sassFiles: {
			"css/dist/admin.css": "css/src/admin.scss",
		},
		pkg: pkg,
	};

	// Used to switch between development and release builds
	if ( [ "release", "artifact", "deploy:trunk", "deploy:master" ].includes( process.argv[ 2 ] ) ) {
		global.developmentBuild = false;
	}

	// Load Grunt configurations and tasks
	loadGruntConfig( grunt, {
		configPath: path.join( process.cwd(), "node_modules/@yoast/grunt-plugin-tasks/config/" ),
		overridePath: path.join( process.cwd(), project.paths.config ),
		data: project,
		jitGrunt: {
			staticMappings: {
				addtextdomain: "grunt-wp-i18n",
				makepot: "grunt-wp-i18n",
				glotpress_download: "grunt-glotpress",
				"update-version": "@yoast/grunt-plugin-tasks",
				"set-version": "@yoast/grunt-plugin-tasks",
			},
		},
	} );
};
