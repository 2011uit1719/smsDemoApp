(function() {
    'use restict';

angular.module('starter.constants',['ionic'])
  .constant('APP_URL',APP_URL());

function APP_URL(ENV){

  var base_url = "";
  var img_url = "";
  var url = {};
  var env = {production:false}

   if (window.location.origin == "http://35.154.180.155") {
    if(window.location.href.indexOf("staging") == -1){
      base_url = "http://35.154.180.155/modernDefence/app/www/server";
    }
    else{
      base_url = "http://35.154.180.155/staging/modernDefence/app/www/server";
    }
  }
  else{
    base_url = "http://localhost/webflax/smsDemoApp/www/server";
  }
  console.log(base_url);

   img_url = "http://35.154.180.155/images"

  url.base_url = base_url;
  url.img_base_url = img_url;

  return url;

}

})();
