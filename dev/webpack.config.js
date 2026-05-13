const path = require('path');
const config = require('ismart-webpack');
module.exports = config(path.resolve(), {
  promo: path.join(path.resolve(), 'src/pages/promo/promo.js'),
});
