var PasswordUtil = {

  //https://stackoverflow.com/a/26528271

  _pattern: /[a-zA-Z0-9_\-\+\.]/,


  _getRandomByte: function () {
    // http://caniuse.com/#feat=getrandomvalues
    if (window.crypto && window.crypto.getRandomValues) {
      var result = new Uint8Array(1);
      window.crypto.getRandomValues(result);
      return result[0];
    }
    else if (window.msCrypto && window.msCrypto.getRandomValues) {
      var result = new Uint8Array(1);
      window.msCrypto.getRandomValues(result);
      return result[0];
    }
    else {
      return Math.floor(Math.random() * 256);
    }
  },

  generate: function (length, lowercase, uppercase, numbers, specialchars) {
    stringPattern = "[";

    if (lowercase)
      stringPattern += "a-z";
    if (uppercase)
      stringPattern += "A-Z";
    if (numbers)
      stringPattern += "0-9";

    

    if (specialchars) {
      specialSet = "!#$%&()*+-./:;=?[]_{}".split("").sort().toString();
      specialSet = specialSet.replace(",", "");

      actualSet = "";

      for(i = 0; i < specialSet.length; i++)
        actualSet += "\\" + specialSet[i];
      

      stringPattern += actualSet;
    }

    stringPattern += "]";

    this._pattern = new RegExp(stringPattern);

    return Array.apply(null, { 'length': length })
      .map(function () {
        var result;
        while (true) {
          result = String.fromCharCode(this._getRandomByte());
          if (this._pattern.test(result)) {
            return result;
          }
        }
      }, this)
      .join('');
  }

};

//# sourceURL=PasswordUtils.js