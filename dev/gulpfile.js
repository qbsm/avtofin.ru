const path = require('path');
const fs = require('fs');
const gulp = require('gulp');
const _ = require('lodash');
const gulpfile = require('ismart-gulpfile');
const packageJSON = require('./package.json');

gulpfile(path.resolve(), packageJSON);

const baseDir = path.resolve();
const pagesSrc = path.join(baseDir, 'src/pages');
const dataDir = path.join(baseDir, '../project/data/content');
const componentsDir = path.join(baseDir, 'src/components');

gulp.task('copy-template-js', (cb) => {
  try {
    const pageDirs = fs.readdirSync(pagesSrc).filter((d) =>
      fs.statSync(path.join(pagesSrc, d)).isDirectory()
    );
    pageDirs.forEach((page) => {
      const tplPath = path.join(pagesSrc, page, `${page}-template.js`);
      const dataPath = path.join(dataDir, `${page}.json`);
      if (!fs.existsSync(tplPath) || !fs.existsSync(dataPath)) return;

      const tpl = fs.readFileSync(tplPath, 'utf8');
      const json = JSON.parse(fs.readFileSync(dataPath, 'utf8'));
      const modules = [
        ...(json.firstScreen || []),
        ...(json.secondaryScreen || []),
      ].reduce((acc, item) => {
        const name = item && item.name;
        if (!name) return acc;
        if (fs.existsSync(path.join(componentsDir, name, `${name}.js`))) {
          acc.push(name);
        }
        return acc;
      }, []);

      const rendered = _.template(tpl)({ modules });
      fs.writeFileSync(path.join(pagesSrc, page, `${page}.js`), rendered);
    });
    cb();
  } catch (err) {
    cb(err);
  }
});
