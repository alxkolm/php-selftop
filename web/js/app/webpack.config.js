module.exports = {
    entry: "./app-dashboard.js",
    output: {
        path: __dirname,
        filename: "app-dashboard-bundle.js"
    },
    module: {
        loaders: [
            { test: /\.js$/, exclude: /node_modules/, loader: "babel-loader"},
            { test: /\.html$/, exclude: /node_modules/, loader: "html-loader"},
            { test: /\.css$/, loader: "style!css" }
        ]
    }
};
