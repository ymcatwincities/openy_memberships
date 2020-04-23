module.exports = {
  "assetsDir": "assets",
  "filenameHashing": false,
  "configureWebpack": {
    "devtool": process.env.NODE_ENV == "development" ? "source-map" : "none",
  },
  "css": {
    "extract": {
      "filename": "assets/css/[name].css"
    },
    "loaderOptions": {
      "sass": {}
    }
  },
  "pluginOptions": {
    "svg": {
      "data": {}
    }
  }
}