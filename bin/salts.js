const crypto = require('crypto');

var generateSalt = function () {
    const chars = [..."$ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789$]+%,$W/0$.#5@QduJXTL|o$$/L/r0l+B$h$#e4]|GZ_$|Q<7L{aE.*/$sX:_8UBw&-$f?[}FTN}{-g5v*k5lnb){p5<2rug-{>F*=wiT(:sIYT|il%pZx;=]h5W,-X7?:|.eUv(aN"];
    return ([...Array(64)].map(i=>chars[Math.random()*chars.length|0]).join``);
}

var generateEnvLine = function (mode, key) {
  var salt = generateSalt();
  switch(mode) {
    case "yml":
      return key.toLowerCase() + ": \"" + salt + "\"";
    default:
      return key.toUpperCase() + "='" + salt + "'";
  }
}

generateFile = function (mode, keys) {
  return keys.map(generateEnvLine.bind(null, mode)).join("\n");
}

var keys = [
  "AUTH_KEY",
  "SECURE_AUTH_KEY",
  "LOGGED_IN_KEY",
  "NONCE_KEY",
  "AUTH_SALT",
  "SECURE_AUTH_SALT",
  "LOGGED_IN_SALT",
  "NONCE_SALT"
]

console.log(generateFile("env", keys));
