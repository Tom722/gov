var gulp = require('gulp'),
    cleancss = require('gulp-clean-css'),
    concat = require('gulp-concat'),
    uglify = require('gulp-uglify'),
    rename = require('gulp-rename'),
    gulpif = require('gulp-if'),
    minimist = require('minimist');

var knownOptions = {
    string: 'env',
    default: {env: process.env.NODE_ENV || 'development'}
};

var options = minimist(process.argv.slice(2), knownOptions);

//合并css
gulp.task('css', function () {
    var css = [
        '../../public/assets/css/bootstrap.min.css',
        '../../public/assets/libs/font-awesome/css/font-awesome.min.css',
        '../../public/assets/addons/ask/css/swiper.min.css',
        '../../public/assets/addons/ask/css/jquery.tagsinput.min.css',
        '../../public/assets/addons/ask/css/jquery.autocomplete.min.css',
        '../../public/assets/addons/ask/css/wysiwyg.css',
        '../../public/assets/addons/ask/css/bootstrap-markdown.css',
        '../../public/assets/addons/ask/css/summernote.css',
        '../../public/assets/addons/ask/css/common.css'
    ];
    return gulp.src(css)    //需要操作的文件
        .pipe(concat('all.css'))    //合并所有js到main.js
        .pipe(rename({suffix: '.min'}))   //rename压缩后的文件名
        .pipe(gulpif(options.env === 'production', cleancss()))    //仅在生产环境时候进行压缩
        .pipe(gulp.dest('../../public/assets/addons/ask/css/'));   //输出文件夹
});

//合并js
gulp.task('js', function () {
    var js = [
        '../../public/assets/libs/jquery/dist/jquery.min.js',
        '../../public/assets/libs/bootstrap/dist/js/bootstrap.min.js',
        '../../public/assets/libs/fastadmin-layer/dist/layer.js',
        '../../public/assets/libs/art-template/dist/template-native.js',
        '../../public/assets/addons/ask/js/taboverride.js',
        '../../public/assets/addons/ask/js/bootstrap-markdown.js',
        '../../public/assets/addons/ask/js/jquery.pasteupload.js',
        '../../public/assets/addons/ask/js/jquery.textcomplete.js',
        '../../public/assets/addons/ask/js/markdown.js',
        '../../public/assets/addons/ask/js/turndown.js',
        '../../public/assets/addons/ask/js/jquery.autocomplete.js',
        '../../public/assets/addons/ask/js/jquery.tagsinput.js',
        '../../public/assets/addons/ask/js/ask.js',
        '../../public/assets/addons/ask/js/common.js'
    ];
    return gulp.src(js)      //需要操作的文件
        .pipe(concat('all.js'))    //合并所有js到main.js
        .pipe(rename({suffix: '.min'}))   //rename压缩后的文件名
        .pipe(gulpif(options.env === 'production', uglify()))     //仅在生产环境时候进行压缩
        .pipe(gulp.dest('../../public/assets/addons/ask/js/'));  //输出
});

//默认命令,在cmd中输入gulp后,执行的就是这个任务(压缩js需要在检查js之后操作)
gulp.task('default',
    gulp.parallel('css', 'js', function (cb) {
        cb();
    })
);
