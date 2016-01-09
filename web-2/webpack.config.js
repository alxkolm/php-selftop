module.exports = {
    entry: "./src/app.js",
    output: {
        path: __dirname,
        filename: "js/app.js"
    },
    module: {
        loaders: [
            { test: /\.js$/, exclude: /node_modules/, loader: "babel-loader"},
            { test: /\.html$/, exclude: /node_modules/, loader: "html-loader"},
            { test: /\.css$/, loader: "style!css" }
        ]
    },
    devtool: 'source-map'
};
