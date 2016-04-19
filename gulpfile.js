// ## Globals
var gulp = require('gulp');
var _ = {
	argv: require('minimist')(process.argv.slice(2))
	, stylus: require('gulp-stylus')
	, autoprefixer: require('gulp-autoprefixer')
	, browserSync: require('browser-sync').create()
	, changed: require('gulp-changed')
	, concat: require('gulp-concat')
	, flatten: require('gulp-flatten')
	, gulpif: require('gulp-if')
	, imagemin: require('gulp-imagemin')
	, jshint: require('gulp-jshint')
	, lazypipe: require('lazypipe')
	, merge: require('merge-stream')
	, cssnano: require('gulp-cssnano')
	, plumber: require('gulp-plumber')
	, rev: require('gulp-rev')
	, runSequence: require('run-sequence')
	, sourcemaps: require('gulp-sourcemaps')
	, uglify: require('gulp-uglify')
	, manifest: require('asset-builder')('./assets/manifest.json')
	, wiredep: require('wiredep').stream
	, preprocess: require('gulp-preprocess')
	, print: require('gulp-print')
	, util: require('gulp-util')
	, del: require('del')
	, cmq: require('gulp-combine-mq')
};

var paths = _.manifest.paths
, config = _.manifest.config || {}
, globs = _.manifest.globs
, project = _.manifest.getProjectGlobs();

var revManifest = paths.dist + 'assets.json';

// CLI options
var CLIOpts = {
  // Enable static asset revisioning when `--production`
  rev: _.argv.production || _.argv.p,
  // Disable source maps when `--production`
  maps: _.argv.maps,
  // Do not minify files when '-d'
  assetdebug: _.argv.d,
  // Start BroswerSync when '--sync'
  sync: _.argv.sync
};

// Path to the compiled assets manifest in the dist directory

var cssTasks = function(filename) 
{
	return _.lazypipe()
	.pipe(_.plumber)
	.pipe(function()
	{
		return _.gulpif(CLIOpts.maps, _.sourcemaps.init());
	})
	.pipe(function()
	{
		return _.gulpif('*.styl', _.stylus());
	})
	.pipe(_.concat, filename)
	.pipe(_.autoprefixer, { browsers: config.prefixes })
	.pipe(_.cmq, {beautify:false})
	.pipe(function()
	{
		return _.gulpif(!CLIOpts.assetdebug, _.cssnano());
	})
	.pipe(function()
	{
		return _.gulpif(CLIOpts.rev, _.rev());
	})
	.pipe(function()
	{
		return _.gulpif(CLIOpts.maps, _.sourcemaps.write('.'));
	})();
};

var jsTasks = function(filename)
{
	return _.lazypipe()
	.pipe(_.preprocess)
	.pipe(function() 
	{
		return _.gulpif(CLIOpts.maps, _.sourcemaps.init());
	})
	.pipe(_.concat, filename)
	.pipe(function()
	{
		return _.gulpif(!CLIOpts.assetdebug, _.uglify());
	})
	.pipe(function()
	{
		return _.gulpif(CLIOpts.rev, _.rev());
	})
	.pipe(function() 
	{
		return _.gulpif(CLIOpts.maps, _.sourcemaps.write('.'));
	})();
};

var writeToManifest = function(directory) 
{
	return _.lazypipe()
	.pipe(gulp.dest, paths.dist + directory)
	.pipe(_.browserSync.stream, {match: '**/*.{js,css}'})
	.pipe(_.rev.manifest, revManifest, {
		base: paths.dist,
		merge: true,
	})
	.pipe(gulp.dest, paths.dist)();
};

/* Tasks */
gulp.task('styles', ['wiredep'], function() 
{
	var merged = _.merge();
	_.manifest.forEachDependency('css', function(dep) 
	{
		var cssTasksInstance = cssTasks(dep.name);
		merged.add(gulp.src(dep.globs)
			.pipe(cssTasksInstance));
	});
	return merged
	.pipe(writeToManifest('styles'));
});

gulp.task('scripts', ['jshint'], function() 
{
	var merged = _.merge();
	_.manifest.forEachDependency('js', function(dep) 
	{
		merged.add(
			gulp.src(dep.globs)
			.pipe(jsTasks(dep.name))
			);
	});
	return merged
	.pipe(writeToManifest('scripts'));
});

gulp.task('fonts', function() 
{
	return gulp.src(globs.fonts)
	.pipe(_.flatten())
	.pipe(gulp.dest(paths.dist + 'fonts'))
	.pipe(_.browserSync.stream());
});

gulp.task('images', function() 
{
	return gulp.src(globs.images)
	.pipe(_.imagemin(
	{
		progressive: true,
		interlaced: true,
		svgoPlugins: [{removeUnknownsAndDefaults: false}, {cleanupIDs: false}]
	}))
	.pipe(gulp.dest(paths.dist + 'images'))
	.pipe(_.browserSync.stream());
});

gulp.task('jshint', function() 
{
	return gulp.src(['bower.json', 'gulpfile.js'].concat(project.js))
	.pipe(_.jshint({"laxcomma": true}))
	.pipe(_.jshint.reporter('jshint-stylish'))
	.pipe(_.jshint.reporter('fail'));
});

gulp.task('clean', _.del.bind(null, [paths.dist]));

gulp.task('watch', function() 
{
	//if(!CLIOpts.rev)
	//	_.del('./' + paths.dist + 'assets.json');

	if(CLIOpts.sync)
	{
		_.browserSync.init(
		{
			files: ['{lib,templates}/**/*.php', '*.php', '.html'],
			proxy: config.devUrl,
			snippetOptions: 
			{
				whitelist: ['/wp-admin/admin-ajax.php'],
				blacklist: ['/wp-admin/**']
			}
		});
	}
	gulp.watch([paths.source + 'styles/**/*'], ['styles']);
	gulp.watch([paths.source + 'scripts/**/*'], ['jshint', 'scripts']);
	gulp.watch([paths.source + 'fonts/**/*'], ['fonts']);
	gulp.watch([paths.source + 'images/**/*'], ['images']);
	gulp.watch(['bower.json', 'assets/manifest.json'], ['build']);
});

gulp.task('build', ['clean'], function(callback) 
{
	_.runSequence('styles',
		'scripts',
		['fonts', 'images'],
		callback);
});

gulp.task('wiredep', function() 
{
	return gulp.src(project.css)
	.pipe(_.wiredep())
	.pipe(_.changed(paths.source + 'styles', 
	{
		hasChanged: _.changed.compareSha1Digest
	}))
	.pipe(gulp.dest(paths.source + 'styles'));
});

gulp.task('default', function() { gulp.start('build'); });