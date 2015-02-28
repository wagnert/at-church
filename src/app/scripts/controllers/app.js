'use strict';

/**
 * @ngdoc function
 * @name srcApp.controller:AppCtrl
 * @description
 * # AppCtrl
 * Controller of the srcApp
 */
angular.module('srcApp')
  .controller('AppCtrl', function ($scope, AuthService) {
    $scope.currentUser = null;
    $scope.isAuthenticated = AuthService.isAuthenticated;
    $scope.setErrorMessage = function (message) {
      $scope.errorMessage = message;
    };
    $scope.setCurrentUsername = function (username) {
      $scope.currentUsername = username;
    };
  });
