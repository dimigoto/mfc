const path = require("path");

module.exports = (compileConfig) => {
    return [{
        entry: {
            mfc: path.resolve(compileConfig.module.path, './src/js/app.js'),
            mfc_style: path.resolve(compileConfig.module.path, './src/scss/app.scss'),
            expromptum_style: path.resolve(compileConfig.module.path, './src/scss/expromptum.css'),
        },
    }];
};