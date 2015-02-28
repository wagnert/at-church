'use strict';

/**
 * @ngdoc service
 * @name srcApp.Authservice
 * @description
 * # Authservice
 * Service in the srcApp.
 */
angular.module('srcApp')
.service('AuthService', function ($http, Session) {
  var login = function (credentials) {
    return $http
      .post('/index.do/login', credentials)
      .then(function (res) {
          Session.create(res.data.id, res.data.username);
          return res.data.username;
      });
  };
  var isAuthenticated = function () {
    return !!Session.id;
  };
  return {
    login: login,
    isAuthenticated: isAuthenticated
  };
});
