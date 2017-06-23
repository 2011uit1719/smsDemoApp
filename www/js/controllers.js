/* global angular, document, window */
'use strict';

angular.module('starter.controllers', ['ionic'])

.controller('AppCtrl', function($scope, $http, $location, $ionicModal, $ionicPopover, $timeout, AuthService, APP_URL, cTeacher, $rootScope) {
    // Form data for the login modal
    $scope.loginData = {};
    $scope.isExpanded = false;
    $scope.hasHeaderFabLeft = false;
    $scope.hasHeaderFabRight = false;

    var navIcons = document.getElementsByClassName('ion-navicon');
    for (var i = 0; i < navIcons.length; i++) {
        navIcons.addEventListener('click', function() {
            this.classList.toggle('active');
        });
    }

    ////////////////////////////////////////
    // Layout Methods
    ////////////////////////////////////////

    $scope.hideNavBar = function() {
        document.getElementsByTagName('ion-nav-bar')[0].style.display = 'none';
    };

    $scope.showNavBar = function() {
        document.getElementsByTagName('ion-nav-bar')[0].style.display = 'block';
    };

    $scope.noHeader = function() {
        var content = document.getElementsByTagName('ion-content');
        for (var i = 0; i < content.length; i++) {
            if (content[i].classList.contains('has-header')) {
                content[i].classList.toggle('has-header');
            }
        }
    };

    $scope.setExpanded = function(bool) {
        $scope.isExpanded = bool;
    };

    $scope.setHeaderFab = function(location) {
        var hasHeaderFabLeft = false;
        var hasHeaderFabRight = false;

        switch (location) {
            case 'left':
                hasHeaderFabLeft = true;
                break;
            case 'right':
                hasHeaderFabRight = true;
                break;
        }

        $scope.hasHeaderFabLeft = hasHeaderFabLeft;
        $scope.hasHeaderFabRight = hasHeaderFabRight;
    };

    $scope.hasHeader = function() {
        var content = document.getElementsByTagName('ion-content');
        for (var i = 0; i < content.length; i++) {
            if (!content[i].classList.contains('has-header')) {
                content[i].classList.toggle('has-header');
            }
        }

    };

    $scope.hideHeader = function() {
        $scope.hideNavBar();
        $scope.noHeader();
    };

    $scope.showHeader = function() {
        $scope.showNavBar();
        $scope.hasHeader();
    };

    $scope.clearFabs = function() {
        var fabs = document.getElementsByClassName('button-fab');
        if (fabs.length && fabs.length > 1) {
            fabs[0].remove();
        }
    };

    var base_url = APP_URL.base_url;
    $scope.logout = function () {

        AuthService.logout(function(isLogout) {
          if(isLogout){
            $location.path("app/login");
          }
        })
      
    }
})

.controller('LoginCtrl', function(
  $scope, $http, $timeout, $location, 
  APP_URL, cTeacher, ionicMaterialInk, AuthService) {
    
    
    $timeout(function(){
      $scope.$parent.hideHeader();
    })
    $scope.$parent.clearFabs();
    ionicMaterialInk.displayEffect();
    var base_url = APP_URL.base_url;

    $scope.user = {};
    $scope.loginForm = {};


    AuthService.is_user_saved(function(result) {
        if(result.valid){ 
            $location.path("app/profile"); 
        }
    })


    $scope.do_login = function() {

      if(($scope.user.username == "") || ($scope.user.password == "")){
        $scope.loginForm.error = "Fill all the fields";
      }
      else
      {
          AuthService.do_login($scope.user, function(result) {
              if(result.valid){
                delete $scope.loginForm.error;

                $location.path("app/profile");
              }
              else {
                $scope.loginForm.error = result.error;
              }
          })
      }
    }

})

.controller('FriendsCtrl', function($scope, $http, $timeout, $filter, $location, $state, $ionicPopup, $ionicLoading, students, AuthService, APP_URL, cTeacher, ionicMaterialInk, ionicMaterialMotion) {
    
    ionicMaterialMotion.fadeSlideInRight();

    // Set Ink
    ionicMaterialInk.displayEffect();

    // get current date
    var base_url = APP_URL.base_url;
    $scope.date = $filter("date")(Date.now(), 'dd-MM-yyyy');
    $scope.sms = {type:"Absent sms"}
    $scope.customSMS = {msg:""}
    $scope.teacherDetails = cTeacher.details;
    

    if(AuthService.isUserAuthorize()) {  
        hideLoader()
        ini()    
    }
    else{
        $location.path("app/login");
    }
    

    // console.log($scope.classDetails);

    $scope.select_all = function (value) {
      for (var i = 0; i < $scope.classDetails.students.length; i++) {
        $scope.classDetails.students[i]['absent'] = value;
      }
    }

    $scope.save = function() {
      var isCustomSms=false, emptyCustomSms=true, list=[];

      if($scope.sms.type == "Custom sms"){
        isCustomSms = true;
        if ($scope.customSMS.msg != "") {
          emptyCustomSms = false;
        }
      }


      if (isCustomSms && emptyCustomSms) {
        var alertPopup = $ionicPopup.alert({
              title: 'Error',
              template: "Please enter your custom sms"
        });
      }
      else{
        showLoader();
        get_absent_stds().forEach(function(student) {
                student.date = $scope.date;

                if (isCustomSms && !emptyCustomSms) {
                    student.sms = $scope.customSMS.msg;
                } else {
                    student.sms = "Your child " + student.name + " is absent today";
                }

                delete student.absent;
                list.push(student);
            }, this);
      }

      if(list.length){
        var data = {
            auth_token:AuthService.getAuthToken(),
            students:list,
            teacher_id:cTeacher.id
        }
          var promise;
          if (isCustomSms && !emptyCustomSms) {
            promise = $http.post(base_url+"/students/sendCustomSms.php", data);
          }
          else {
            promise = $http.post(base_url+"/students/saveAbsentStudents.php", data);
          }
          promise.then(function(response) {
            var result = response.data;
            hideLoader()            
            if(result.valid){
              var alertPopup = $ionicPopup.alert({
                title: 'Success',
                template: result.msg
              });
              $location.path("app/profile");
            }
            else {
              var alertPopup = $ionicPopup.alert({
                title: 'Error',
                template: result.error
              });
            }
          });
      }


    }

    $scope.fetch_latest_students = function() {
        students.get_latest(function (list) {
            $scope.classDetails = list;
        })
    }

    function ini() {
        students.get_all(function (list) {
            $scope.classDetails = list;
        })
    }

    function hideLoader() {
        $ionicLoading.hide();
    }
    function showLoader() {
        $ionicLoading.show({
          template: '<ion-spinner icon="android"></ion-spinner>'
        });
    }
    function get_absent_stds() {
        var absentStds = [];
        $scope.classDetails.students.forEach(function(student) {
            if(student.absent){ absentStds.push(Object.assign({},student)); }
        }, this);

        return absentStds;
    }
    function get_students(id) {
        var data = {
            auth_token:AuthService.getAuthToken(),
            class_id:id
        }

        $http.get(base_url+"/standard/getAllStudentsInClass.php", {params:data})
            .then(function(response) {
            $scope.classDetails = response.data.data;
            localStorage.setItem(id, JSON.stringify(response.data.data))
            //$state.reload();
            })
    }
    
})

.controller('ProfileCtrl', function($scope, $http, $timeout, $state, $location, $ionicPopup, APP_URL, AuthService, teacher, cTeacher, ionicMaterialMotion, ionicMaterialInk) {
    
    $timeout(function(){
      $scope.$parent.showHeader();
    })
    $scope.$parent.clearFabs();
    // $scope.isExpanded = false;
    // $scope.$parent.setExpanded(false);
    // $scope.$parent.setHeaderFab(false);

    // Set Motion
    $timeout(function() {
        ionicMaterialMotion.slideUp({
            selector: '.slide-up'
        });
    }, 300);

    $timeout(function() {
        ionicMaterialMotion.fadeSlideInRight({
            startVelocity: 3000
        });
    }, 700);

    // Set Ink
    ionicMaterialInk.displayEffect();
    var base_url = APP_URL.base_url;
    $scope.imgBaseUrl = APP_URL.img_base_url;
    $scope.teacher = {};



    if ( AuthService.isUserAuthorize() ) 
    {
        teacher.get_detail(function (result) {
            if(result.valid){
                $scope.teacher = cTeacher.details
            }
            else{
                $ionicPopup.alert({
                    title: 'Error',
                    template: result.error
                });
            }
        })
    }
    else{
        $location.path("app/login");
    }

    
    $scope.show_class_details = function (id) {
        cTeacher.selectedClassId = id;
        $location.path("app/friends")
    }

})

.controller('ChangePasswordCtrl', function($scope, $http, $timeout, $state, $location, $ionicPopup, APP_URL, AuthService, cTeacher, ionicMaterialMotion, ionicMaterialInk) {
    $timeout(function(){
      $scope.$parent.showHeader();
    })
    $scope.$parent.clearFabs();
    // $scope.isExpanded = false;
    // $scope.$parent.setExpanded(false);
    // $scope.$parent.setHeaderFab(false);

    // Set Motion
    $timeout(function() {
        ionicMaterialMotion.slideUp({
            selector: '.slide-up'
        });
    }, 300);

    $timeout(function() {
        ionicMaterialMotion.fadeSlideInRight({
            startVelocity: 3000
        });
    }, 700);

    // Set Ink
    ionicMaterialInk.displayEffect();
    var base_url = APP_URL.base_url;
    $scope.user = {
        oldPassword:"",
        newPassword:"",
        reNewPassword:''
    };

    if(!AuthService.isUserAuthorize()) {  
        $location.path("app/login");
    }

    $scope.change_password = function () {
        if(is_form_valid()){
            
            $scope.user.id = cTeacher.id;
            $scope.user.auth_token = AuthService.getAuthToken()

            $http.post(base_url+"/user/resetCode.php", $scope.user).then(function (response) {
                if(response.status === 200 && response.data.valid){
                    $ionicPopup.alert({
                        title: 'Success',
                        template: response.data.msg
                    });
                    $location.path("app/profile");
                }
                else{
                    $ionicPopup.alert({
                        title: 'Error',
                        template: response.data.error
                    });
                }
            })
        
        }
    }

    function is_form_valid() {
        if($scope.user.oldPassword == "" || $scope.user.newPassword == "" || $scope.user.reNewPassword == ""){
            alert("fill all the fields");
            return false;
        }
        else if($scope.user.newPassword != $scope.user.reNewPassword ){
            alert("Re-enter password does not match");
            return false;
        }
        else if($scope.user.newPassword.length <8 ){
            alert("New password length cannot be less then 8");
            return false;
        }
        else{
            return true;
        }
    }
})


.controller('ReportCtrl', function($scope, $http, $timeout, $state, $location, $ionicPopup, $ionicModal, $ionicLoading, APP_URL, students, teacher, AuthService, cTeacher, ionicMaterialMotion, ionicMaterialInk) {
    
    $timeout(function(){
      $scope.$parent.showHeader();
    })
    $scope.$parent.clearFabs();
    // $scope.isExpanded = false;
    // $scope.$parent.setExpanded(false);
    // $scope.$parent.setHeaderFab(false);

    // Set Motion
    $timeout(function() {
        ionicMaterialMotion.slideUp({
            selector: '.slide-up'
        });
    }, 300);

    $timeout(function() {
        ionicMaterialMotion.fadeSlideInRight({
            startVelocity: 3000
        });
    }, 700);

    // Set Ink
    ionicMaterialInk.displayEffect();
    
    
    var base_url = APP_URL.base_url


    $scope.classes = {};
    $scope.report = {selectedClass:"", selectedStudent:"", type:""};
    $scope.absents = {total:-1}
    


    

    $ionicModal.fromTemplateUrl('my-modal.html', {
        scope: $scope,
        animation: 'slide-in-up'
    }).then(function(modal) {
        $scope.modal = modal;
    });
    $scope.openModal = function () {
        $scope.modal.show();
    };
    $scope.hideModal = function () {
        $scope.modal.hide();
    };

    if ( !AuthService.isUserAuthorize() ) 
    {
        $location.path("app/login");
    }
    else
    {
        teacher.get_detail(function (result) {
            if(result.valid){
                $scope.classes = cTeacher.details.classes
                console.log($scope.classes)
            }
            else{
                showError(result.error);
            }
        })
    }

    $scope.showClassReports = function(){

        if($scope.report.selectedClass == ""){
            showError("Select Class");
        }
        else if( $scope.report.selectedStudent == "" ){
            showError("Select Student");
        }
        else if($scope.report.smsType == undefined){
            showError("Select Sms Type");
        }
        else if($scope.report.type == ""){
            showError("Select Report Type");
        }
        else if( ($scope.report.type == "day" || $scope.report.type == "week")  && 
        $scope.report.selectedDate == undefined){
            showError("Select Date");
        }
        else if( $scope.report.type == "month"  && $scope.report.selectedMonth == undefined){
            showError("Select Month");
        }
        else{
            showLoader()
            var report = Object.assign({}, $scope.report);

            if(report.type == "day" || report.type == "week"){
                report.selectedDate = convertDate(report.selectedDate)
            }
            report.auth_token = AuthService.getAuthToken();
            report.teacher_id = cTeacher.id;

            $http.get( base_url + "/standard/getReport.php", {params:report}).then(function (response) {
                $scope.absents = response.data.data
                
                hideLoader();
            })
        }
    }

    $scope.showStudentList = function (class_id) {
        showLoader()
        students.get_all(function (list) {
            hideLoader();
            $scope.students = list.students;
        }, class_id)
    }

    function showError(msg) {
        $ionicPopup.alert({
            title: 'Error',
            template: msg
        });
    }
    function convertDate(inputFormat) {
        function pad(s) { return (s < 10) ? '0' + s : s; }
        var d = new Date(inputFormat);
        return [pad(d.getDate()), pad(d.getMonth()+1), d.getFullYear()].join('-');
    }

    function hideLoader() {
        $ionicLoading.hide();
    }
    function showLoader() {
        $ionicLoading.show({
          template: '<ion-spinner icon="android"></ion-spinner>'
        });
    }

});
