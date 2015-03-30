'use strict';

/**
 * @ngdoc function
 * @name srcApp.controller:LoginCtrl
 * @description
 * # LoginCtrl
 * Controller of the srcApp
 */
angular.module('srcApp')
  .controller('LoginCtrl', function ($scope, $location, AuthService) {
    $scope.credentials = {
      username: '',
      password: ''
    };
    $scope.login = function (credentials) {
      AuthService.login(credentials).then(function (username) {
        $scope.setErrorMessage(null);
        $scope.setCurrentUsername(username);
        $location.path('/');
      }, function (response) {
    	  console.log(response);
        $scope.setErrorMessage(response.data.error.message);
      });
    };
  });
