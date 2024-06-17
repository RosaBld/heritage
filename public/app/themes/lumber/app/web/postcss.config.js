module.exports = {
    plugins: [
        // 'postcss-import': {},
        // require('precss')(),
        // require('autoprefixer')(),
        require('postcss-import')({}),
        require('postcss-preset-env')(),
        // require('autoprefixer')(),
        require('cssnano')()
    ]
}
