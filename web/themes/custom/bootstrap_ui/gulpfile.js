let gulp = require('gulp'),
  settings = require('./gulp-settings'),
  sass = require('gulp-sass'),
  concat = require('gulp-concat'),
  minify = require('gulp-minify'),
  sourcemaps = require('gulp-sourcemaps'),
  cleanCss = require('gulp-clean-css'),
  rename = require('gulp-rename'),
  postcss = require('gulp-postcss'),
  autoprefixer = require('autoprefixer'),
  browserSync = require('browser-sync').create()

const paths = {
  scss: {
    src: './scss/style.scss',
    dest: './css',
    watch: './scss/**/*.scss',
    bootstrap: './node_modules/bootstrap/scss/bootstrap.scss'
  },
  js: {
    bootstrap: './node_modules/bootstrap/dist/js/bootstrap.min.js',
    jquery: './node_modules/jquery/dist/jquery.min.js',
    popper: 'node_modules/popper.js/dist/umd/popper.min.js',
    watch: './js/components/*.js',
    dest: './js'
  }
}


// Compile sass into CSS & auto-inject into browsers
function styles () {
  return gulp.src([paths.scss.bootstrap, paths.scss.src])
    .pipe(sourcemaps.init())
    .pipe(sass().on('error', sass.logError))
    .pipe(postcss([autoprefixer({
      browsers: [
        'Chrome >= 35',
        'Firefox >= 38',
        'Edge >= 12',
        'Explorer >= 10',
        'iOS >= 8',
        'Safari >= 8',
        'Android 2.3',
        'Android >= 4',
        'Opera >= 12']
    })]))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest(paths.scss.dest))
    .pipe(cleanCss())
    .pipe(rename({ suffix: '.min' }))
    .pipe(gulp.dest(paths.scss.dest))
    .pipe(browserSync.stream())
}

// Move the javascript files into our js folder
function js () {
  return gulp.src([paths.js.bootstrap, paths.js.jquery, paths.js.popper, paths.js.watch])
    // .pipe(concat('bundle.js'))
    // .pipe(minify({
    //   ext:{
    //     min:'.js'
    //   },
    //   noSource: true
    // }))
    .pipe(gulp.dest(paths.js.dest))
    .pipe(browserSync.stream())
}

// Static Server + watching scss/html files
function serve () {
  browserSync.init({
    proxy: settings.proxy,
  });

  gulp.watch([paths.scss.watch, paths.scss.bootstrap], styles).on('change', browserSync.reload)
}

gulp.task('watch', function() {
  browserSync.init({
    notify: false,
    proxy: settings.proxy,
    ghostMode: false
  });

  gulp.watch([paths.scss.watch, paths.scss.bootstrap], styles).on('change', browserSync.reload)
  gulp.watch([paths.js.dest]).on('change', browserSync.reload);
});

// Watch CSS + JS without browsersync - useful for admin mode
gulp.task('watch-basic', function() {
  gulp.watch([paths.scss.watch, paths.scss.bootstrap], styles);
  gulp.watch([paths.js.dest]);
});

gulp.task('watch-css', function() {
  browserSync.init({
    notify: false,
    proxy: settings.proxy,
    ghostMode: false
  });

  gulp.watch([paths.scss.watch, paths.scss.bootstrap], styles).on('change', browserSync.reload);
});

gulp.task('sass', styles);
gulp.task('js', js);

gulp.task('watch-js', function() {
  browserSync.init({
    notify: false,
    proxy: settings.proxy,
    ghostMode: false
  });

  gulp.watch([paths.js.dest]).on('change', browserSync.reload);
});

const build = gulp.series(styles, gulp.parallel(js, serve))

exports.styles = styles
exports.js = js
exports.serve = serve

exports.default = build
