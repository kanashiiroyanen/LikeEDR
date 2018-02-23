var express = require('express');
var fileupload = require('express-fileupload');
var fs = require('fs');

var router = express.Router();
var savedir = '/var/tmp';

var safepath_rule = /^([0-9A-Za-z._-]+)\.zip$/;
var unsafe_rule = /\.\./;

var escape_json = function(s) {
    return s.replace(/\\n/g, "\\n")
          .replace(/\\'/g, "\\'")
          .replace(/\\"/g, '\\"')
          .replace(/\\&/g, "\\&")
          .replace(/\\r/g, "\\r")
          .replace(/\\t/g, "\\t")
          .replace(/\\b/g, "\\b")
          .replace(/\\f/g, "\\f");
};

router.use(fileupload());

/* GET users listing. */
router.get('/', function(req, res, next) {
  id = req.params.id
  act = req.params.act
  res.send('post file here.' + id + " - " + id);
});

function error_msg(data) {
    return '{"result":"fail", "reason": "' + escape_json(data) + '"}';
}

router.post('/', function(req, res, next) {
console.log("--- check0 ---", req.files);
    if (!req.files) {
        next(error_msg("no upload files"));
    } else if(!req.files.log) {
        next(error_msg("upload name is invalid"));
    } else {
console.log("--- check ---", req.files);
        var tmpfile  = req.files.log;
        var savename = tmpfile.name;
        var savepath = savedir + '/' + savename;
console.log("check2", savepath);
        tmpfile.mv(savepath, function(error) {
            if(error) {
                next(error_msg(error));
            } else {
                res.send('{"result":"ok", "name":"' + savepath + '"}');
            }
        });
    }
});

module.exports = router;
