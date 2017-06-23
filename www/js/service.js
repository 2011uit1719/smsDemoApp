(function() {

angular.module('starter.service', ['ionic'])



.service('AuthService', function ($http, APP_URL, cTeacher, $rootScope) {

    var base_url = APP_URL.base_url;
    var userAuthorize = false;
    var authToken = "";

    this.do_login = function (user, callback) {

        $http.get(base_url+"/user/logout.php").then(function(response){
        
          return response.data
        
        }).then(function (data) {
            
            return $http.get(base_url+"/form/getCsrfToken.php").then(function(response){
                return response.data
            })
        
        }).then(function (data){

            user.csrf_token = data.token;
            return $http.post(base_url+"/user/login.php", user).then(function(response){
                return response.data
            })

        }).then(function (result) {
            
            if(result.valid)
            {
                setUserAuthorize(true);

                cTeacher.reset();
                cTeacher.id = result.data.teacher_id;
                setAuthToken(result.data.auth_token);
                localStorage.setItem("isLogin",JSON.stringify({valid:true, remember_code:result.data.remember_code}))
            }

            callback(result)
            
        })
    
    }// do login fn end

    
    
    this.is_user_saved = function(callback){
      
      if(localStorage.getItem("isLogin") !== null)
      {
        var isLogin = JSON.parse(localStorage.getItem("isLogin"));

        $http.post(base_url+"/user/validateRememberme.php", {remember_code:isLogin.remember_code})
            .then(function (response) {
                if(response.status === 200 && response.data.valid){
                    cTeacher.id = response.data.data.teacher_id
                    setUserAuthorize(true);
                    setAuthToken(response.data.data.auth_token);
                }
                callback(response.data)
            })
      }
      else{
          callback({valid:false})
      }
    }// is user saved fn end



    this.logout = function (callback) {
        $http.post(base_url+"/user/logout.php").then(function (response) {
            
            if((response.status === 200) && response.data.valid)
            {
                $rootScope.fromLogout = true;
                localStorage.removeItem("isLogin")
                localStorage.removeItem(cTeacher.details.class_id)

                console.log(JSON.parse(localStorage.getItem("isLogin")))
                
                cTeacher.reset();
                setUserAuthorize(false)
                callback(true)
            }
            else{
                callback(false)
            }
        })
    }

    this.isUserAuthorize = function () {
        return userAuthorize;
    }

    function setUserAuthorize(value) {
        userAuthorize = value;
    }

    function setAuthToken(token) {
        authToken = token;
    }

    this.getAuthToken = function() {
        return authToken;
    }


}) // auth service ends here

.service('cTeacher', function (){
    
    this.id ="";
    this.details = {};
    this.studentList = [];
    this.selectedClassId = "";

    this.reset = function (){
      this.id ="";
      this.details = {};
      this.studentList = [];
    }


}) //teacher service ends here


.service('teacher', function ($http, APP_URL, cTeacher, AuthService){
    var base_url = APP_URL.base_url;
    var vm = this;

    this.get_detail = function(callback){
      if ( isEmpty(cTeacher.details) ) {
          
          var data = {
            auth_token:AuthService.getAuthToken(),
            teacher_id:cTeacher.id
          }

          $http.get(base_url+"/teacher/getDetails.php", {params:data}).then(function (response) {
              var result = response.data;
              var teacher = result.data.userDetails;
              
              if(result.valid){
                cTeacher.details = teacher;
              }

              callback(result)
          })
      }
      else{
            callback({valid:true})
      }
      
    }
    function isEmpty(obj) {
      for(var key in obj) {
          if(obj.hasOwnProperty(key))
              return false;
      }
      return true;
    }

}) //teacher service ends here


.service('students', function ($http, APP_URL, cTeacher, AuthService) {
    var vm = this,
    base_url = APP_URL.base_url;

    this.get_all = function(callback, id = null) {

        if (id == null) {
            id = cTeacher.selectedClassId;
        }

        if (localStorage.getItem( id ) != null) {
            callback(JSON.parse(localStorage.getItem( id )))
        }
        else{
            vm.get_latest(callback)
        }
    }

    this.get_latest = function (callback, id = null) {

        if (id == null) {
            id = cTeacher.selectedClassId;
        }

        var data = {
            auth_token:AuthService.getAuthToken(),
            class_id: id 
        }

        $http.get(base_url+"/standard/getAllStudentsInClass.php", {params:data}).then(function(response) {
            localStorage.setItem(  id  , JSON.stringify(response.data.data))
            callback( response.data.data );
        })
    }
})



})()