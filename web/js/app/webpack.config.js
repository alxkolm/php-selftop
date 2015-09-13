module.exports = {
    entry: "./index.js",
    output: {
        path: __dirname,
        filename: "dist/app.bundle.js"
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
